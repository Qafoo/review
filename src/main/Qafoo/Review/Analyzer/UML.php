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

            $classSpec = array(
                'file'      => str_replace( $path, '', $class->getFileName() ),
                'abstract'  => $class->isAbstract(),
                'interface' => $class->isInterface(),
                'extends'   => array(),
                'uses'      => array(),
            );

            if ( $parent = $class->getParentClass() )
            {
                $classSpec['extends'][] = $parent->getNamespaceName() . '\\' . $parent->getName();
            }

            foreach ( $class->getInterfaces() as $interface )
            {
                $classSpec['extends'][] = $interface->getNamespaceName() . '\\' . $interface->getName();
            }

            foreach ( $class->getMethods() as $method )
            {
                foreach ( $method->getParameters() as $parameter )
                {
                    if ( $usedClass = $parameter->getClass() )
                    {
                        $classSpec['uses'][] = $usedClass->getNamespaceName() . '\\' . $usedClass->getName();
                    }
                }
            }

            $classSpec['uses']  = array_unique( $classSpec['uses'] );
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

            mindist  = 0.1;
            rankdir  = LR;
            splines  = true;
            overlap  = false;
            penwidth = .5;
        ';

        foreach ( $classes as $className => $data )
        {
            $dotInput .= sprintf( '    "%s" [%s]' . PHP_EOL,
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

            foreach ( $data['uses'] as $parent )
            {
                $dotInput .= sprintf( '    "%s" -> "%s" [constraint=false, arrowhead="none", color="#dddddd"]' . PHP_EOL,
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
        $bgColor = '#f9f9f9';
        $color   = '#333333';

        if ( $data['interface'] )
        {
            $bgColor = '#fcf8e3';
        } elseif ( $data['abstract'] )
        {
            $bgColor = '#d9edf7';
        }


        return sprintf(
            'shape="rect", label="%s", style="filled", color="%s", fillcolor="%s", href="%s"',
            addslashes( strlen( $name ) > 30 ? '…' . substr( $name, -29 ) : $name ),
            $color,
            $bgColor,
            addslashes( '/source/' . $data['file'] )
        );
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

