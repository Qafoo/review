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

/**
 * PDepend analyzer class
 *
 * @version $Revision$
 */
class PDepend extends Analyzer implements Displayable
{
    /**
     * List of class metrics provided by pdepend
     *
     * @var array
     */
    protected $classMetrics = array(
        'cis'    => 'Class interface size',
        'cloc'   => 'Comment lines of code',
        'cr'     => 'Code rank',
        'csz'    => 'Class size',
        'dit'    => 'Depth of inheritence tree',
        'eloc'   => 'Executable lines of code',
        'impl'   => 'Number of implemented interfaces',
        'loc'    => 'Lines of code',
        'ncloc'  => 'Non-comment lines of code',
        'nom'    => 'Number of methods',
        'rcr'    => 'Reverse code rank',
        'vars'   => 'Number of defined class properties',
        'varsi'  => 'Number of own and inherited class properties',
        'varsnp' => 'Number of public class properties',
        'wmc'    => 'Weighted Method per Class (Sum of method Cyclomatic Complexity)',
        'wmci'   => 'Weighted Method per Class + inherited WMC',
        'wmcnp'  => 'Weighted Method per Class for all public class methods',
    );

    /**
     * List of method metrics provided by pdepend
     *
     * @var array
     */
    protected $methodMetrics = array(
        'ccn'   => 'Cyclomatic Complexity',
        'ccn2'  => 'Extended Cyclomatic Complexity',
        'cloc'  => 'Comment Lines Of Code',
        'eloc'  => 'Executable Lines Of Code',
        'loc'   => 'Lines Of Code',
        'ncloc' => 'Non Comment Lines Of Code',
        'npath' => 'NPath Complexity',
    );

    /**
     * Thresholds for class metric values
     *
     * @var array
     */
    protected $classTresholds = array(
        'warning' => array(
            'cis'    => 10,
            'cloc'   => 1000,
            'cr'     => 5,
            'csz'    => 50,
            'dit'    => 5,
            'eloc'   => 250,
            'impl'   => 5,
            'loc'    => 1250,
            'ncloc'  => 500,
            'nom'    => 10,
            'rcr'    => 5,
            'vars'   => 10,
            'varsi'  => 15,
            'varsnp' => 10,
            'wmc'    => 50,
            'wmci'   => 50,
            'wmcnp'  => 50,
        ),
        'error' => array(
            'cis'    => 25,
            'cloc'   => 5000,
            'cr'     => 8,
            'csz'    => 100,
            'dit'    => 10,
            'eloc'   => 500,
            'impl'   => 8,
            'loc'    => 1500,
            'ncloc'  => 1000,
            'nom'    => 25,
            'rcr'    => 8,
            'vars'   => 25,
            'varsi'  => 35,
            'varsnp' => 25,
            'wmc'    => 80,
            'wmci'   => 80,
            'wmcnp'  => 80,
        ),
    )
    ;
    /**
     * Thresholds for class metric values
     *
     * @var array
     */
    protected $methodThresholds = array(
        'warning' => array(
            'ccn'   => 10,
            'ccn2'  => 10,
            'cloc'  => 100,
            'eloc'  => 20,
            'loc'   => 100,
            'ncloc' => 20,
            'npath' => 100,
        ),
        'error' => array(
            'ccn'   => 25,
            'ccn2'  => 25,
            'cloc'  => 500,
            'eloc'  => 50,
            'loc'   => 100,
            'ncloc' => 50,
            'npath' => 200,
        ),
    );

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
        $process = new \SystemProcess\SystemProcess( 'pdepend' );
        $process->nonZeroExitCodeException = true;
        $process
            ->argument( '--jdepend-chart=' . $this->resultDir . '/pdepend_jdepend.svg' )
            ->argument( '--overview-pyramid=' . $this->resultDir . '/pdepend_pyramid.svg' )
            ->argument( '--arbit-xml=' . $this->resultDir . '/pdepend_summary.xml' )
            ->argument( $path );

        $process->execute();

        $this->processAnnotations( $path, $this->resultDir . '/pdepend_summary.xml' );
    }

    /**
     * Process annotations from summary XML file
     *
     * @param string $path
     * @param string $summaryXml
     * @return void
     */
    protected function processAnnotations( $path, $summaryXml )
    {
        $doc = new \DOMDocument();
        $doc->load( $summaryXml );
        $xpath = new \DOMXPath( $doc );

        // Replace all pathes in summary.xml with relative pathes
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            $fileNode->setAttribute( 'name', str_replace( $path, '', $fileNode->getAttribute( 'name' ) ) );
        }
        $doc->save( $summaryXml );

        // Find all metrics and emit signals for warnings and errors
        foreach ( $xpath->query( '//class' ) as $classNode )
        {
            $files = $classNode->getElementsByTagName( 'file' );
            if ( $files->length === 0 )
            {
                continue;
            }

            $metrics = $this->getClassMetrics( $classNode );
            foreach ( $metrics as $metric => $value )
            {
                $class = 'warning';
                if ( ( $value > $this->classTresholds['warning'][$metric] ) ||
                     ( (int) ( $class = 'error' ) ) ||
                     ( $value > $this->classTresholds['error'][$metric] ) )
                {
                    $this->gateway->create( new Struct\Annotation(
                        $files->item( 0 )->getAttribute( 'name' ),
                        (int) $classNode->getAttribute( 'startLine' ),
                        null,
                        'pdepend',
                        $class,
                        $this->classMetrics[$metric] . ': ' . $value
                    ) );
                }
            }

            foreach ( $classNode->getElementsByTagName( 'method' ) as $methodNode )
            {
                $metrics = $this->getMethodMetrics( $methodNode );

                foreach ( $metrics as $metric => $value )
                {
                    $class = 'warning';
                    if ( ( $value > $this->methodThresholds['warning'][$metric] ) ||
                         ( (int) ( $class = 'error' ) ) ||
                         ( $value > $this->methodThresholds['error'][$metric] ) )
                    {
                        $this->gateway->create( new Struct\Annotation(
                            $files->item( 0 )->getAttribute( 'name' ),
                            (int) $methodNode->getAttribute( 'startLine' ),
                            null,
                            'pdepend',
                            $class,
                            $this->methodMetrics[$metric] . ': ' . $value
                        ) );
                    }
                }
            }
        }
    }

    /**
     * Get class metrics
     *
     * Return an array with the class metric values from a DOMElement from the
     * summary XML file.
     *
     * @param \DOMElement $element
     * @return array
     */
    protected function getClassMetrics( \DOMElement $element )
    {
        foreach ( array_keys( $this->classMetrics ) as $metric )
        {
            if ( $element->hasAttribute( $metric ) )
            {
                $metrics[$metric] = (float) $element->getAttribute( $metric );
            }
        }

        return $metrics;
    }

    /**
     * Get method metrics
     *
     * Return an array with the method metric values from a DOMElement from the
     * summary XML file.
     *
     * @param \DOMElement $element
     * @return array
     */
    protected function getMethodMetrics( \DOMElement $element )
    {
        foreach ( array_keys( $this->methodMetrics ) as $metric )
        {
            if ( $element->hasAttribute( $metric ) )
            {
                $metrics[$metric] = (float) $element->getAttribute( $metric );
            }
        }

        return $metrics;
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'PDepend',
            'Displays class and method metrics as a tag cloud. This allows you to locate violations quickly.'
        );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'PDepend' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {

    }
}

