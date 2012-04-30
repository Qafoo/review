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
    public function __construct( $resultDir, AnnotationGateway $gateway )
    {
        $this->resultDir = $resultDir;
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
                'file'      => str_replace( $path, '', $class->getFileName() ),
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
            ];

            mindist = 0.1;
            rankdir = LR;
            splines = true;
            overlap = false;
        ';

        foreach ( $classes as $className => $data )
        {
            $dotInput .= sprintf( '    "%s" [shape=plaintext, label=<%s>]' . PHP_EOL,
                addslashes( $className ),
                $this->getClassLabel( $className, $data )
            );

            foreach ( $data['extends'] as $parent )
            {
                $dotInput .= sprintf( '    "%s" -> "%s" [arrowhead="onorman"]' . PHP_EOL,
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
            ->argument( '-o' . $this->resultDir . '/uml.svg' )
            ->argument( $dotFile );
        $process->execute();
    }

    /**
     * Get label for a class node
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    protected function getClassLabel( $name, array $data )
    {
        $bgColor = $data['interface'] || $data['abstract'] ? '#d9edf7' : '#f9f9f9f';

        $html = '<TABLE
                COLOR="#DDDDDD"
                BGCOLOR="' . $bgColor . '"
                BORDER="1"
                CELLSPACING="0"
                CELLPADDING="2"
                CELLBORDER="0"
                HREF="' . htmlentities( '/source/' . $data['file'] ) . '">
            <TR>
                <TD
                    ALIGN="CENTER">';

        $displayName = strlen( $name ) > 30 ? '…' . substr( $name, -29 ) : $name;

        if ( $data['interface'] )
        {
            $html .= '&lt;&lt;interface&gt;&gt;<BR/><B>' . $displayName . '</B>';
        }
        elseif( $data['abstract'] )
        {
            $html .= '<B><I>' . $displayName . '</I></B>';
        }
        else
        {
            $html .= '<B>' . $displayName . '</B>';
        }

        $html .= '</TD></TR></TABLE>';
        return $html;
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
                'diagram' => file_get_contents( $this->resultDir . '/uml.svg' )
            )
        );
    }
}

