<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Analyzer;
use Qafoo\Review\Analyzer;
use Qafoo\Review\AnnotationGateway;
use Qafoo\Review\Struct;
use Qafoo\Review\Displayable;
use Qafoo\RMF;

use pdepend\reflection\Autoloader;
use pdepend\reflection\ReflectionSession;
use pdepend\reflection\resolvers\PearNamingResolver;

/**
 * UML analyzer class
 *
 * @version $Revision$
 */
class UML extends Analyzer implements Displayable
{
    /**
     * Result directory
     *
     * @var string
     */
    protected $resultDir;

    /**
     * HTDocs dir
     *
     * @var string
     */
    protected $htdocsDir;

    /**
     * Annotation gateway
     *
     * @var AnnotationGateway
     */
    protected $gateway;

    /**
     * Create from annotation gateway
     *
     * @param string $resultDir
     * @param AnnotationGateway $gateway
     * @return void
     */
    public function __construct( $resultDir, $htdocsDir, AnnotationGateway $gateway )
    {
        $this->resultDir = $resultDir;
        $this->htdocsDir = $htdocsDir;
        $this->gateway   = $gateway;
    }

    /**
     * Analyze source
     *
     * @param string $path
     * @return void
     */
    public function analyze( $path )
    {
        $classes = $this->parseClassDependencies( $path );
        $this->renderDiagram( $classes );
    }

    /**
     * Parse and return class dependencies
     *
     * @param string $path
     * @return array
     */
    protected function parseClassDependencies( $path )
    {
        $session = ReflectionSession::createDefaultSession(
            new PearNamingResolver( array( $path ) )
        );
        $query   = $session->createDirectoryQuery();
        $classes = array();
        foreach ( $query->find( $path ) as $class )
        {
            if ( strpos( $class->getFileName(), 'tests/' ) !== false )
            {
                continue;
            }

            $fullName = $class->getNamespaceName() . '\\' . $class->getName();

            $parent = $class->getParentClass();
            $classSpec = array(
                'extends'   => $parent ? array( $parent->getNamespaceName() . '\\' .$parent->getName() ) : array(),
                'abstract'  => $class->isAbstract(),
                'interface' => $class->isInterface(),
            );

            foreach ( $class->getInterfaces() as $interface )
            {
                $classSpec['extends'][] = $interface->getNamespaceName() . '\\' . $interface->getName();
            }

            // @TODO: Iterate over methods to find usage connections

            $classes[$fullName] = $classSpec;
        }

        file_put_contents( $this->resultDir . '/classes.php', "<?php\n\nreturn " . var_export( $classes, true ) . ";\n\n" );
        return $classes;
    }

    /**
     * Render a class diagram using dot
     *
     * @param array $classes
     * @return void
     */
    protected function renderDiagram( array $classes )
    {
        $dotInput = 'digraph ClassDiagram {
            node [
                fontname  = Arial,
                fontcolor = "#2e3436",
                fontsize  = 10,

                style     = filled,
                color     = "#2e3436",
                fillcolor = "#eeeeef"
            ];

            mindist = 0.4;
            rankdir = LR;
            splines = true;
            overlap = false;
        ';

        foreach ( $classes as $className => $data )
        {
            $dotInput .= sprintf( '    "%s" [shape=%s, label="%s"]' . PHP_EOL,
                addslashes( $className ),
                ( $data['interface'] ? 'oval' : ( $data['abstract'] ? 'hexagon' : 'box' ) ),
                addslashes( strlen( $className ) > 30 ? '…' . substr( $className, -29 ) : $className )
            );

            foreach ( $data['extends'] as $parent )
            {
                $dotInput .= sprintf( '    "%s" -> "%s"' . PHP_EOL,
                    addslashes( $className ),
                    addslashes( $parent )
                );
            }
        }

        $dotInput .= '}';

        file_put_contents( $dotFile = $this->resultDir . '/uml.dot', $dotInput );

        $process = new \SystemProcess\SystemProcess( 'dot' );
        $process
            ->argument( '-Tsvg' )
            ->argument( '-o' . $this->htdocsDir . '/images/uml.svg' )
            ->argument( $dotFile );
        $process->execute();
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'UML',
            'Displays an UML diagram of the available classes'
        );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'UML' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        return new Struct\Response(
            'uml.twig',
            array(
            )
        );
    }
}

