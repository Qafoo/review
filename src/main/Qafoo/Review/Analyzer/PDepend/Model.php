<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Analyzer\PDepend;

use Qafoo\Review\Struct;
use Qafoo\Review\CodeProcessorFactory;

/**
 * PDepend analyzer class
 *
 * @version $Revision$
 * @license APGLv3
 */
class Model
{
    /**
     * List of class metrics provided by pdepend
     *
     * @var array
     */
    protected $classMetrics = array(
        'ce'     => 'Efferent Coupling',
        'ca'     => 'Afferent Coupling',
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
            'ca'     => 500,
            'ce'     => 3,
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
            'ca'     => 500,
            'ce'     => 5,
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
    );

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
     * Path to source code
     *
     * @var string
     */
    protected $source;

    /**
     * Code processor factory
     *
     * @var CodeProcessorFactory
     */
    protected $factory;

    /**
     * DOMDocument representing pdepend XML file
     *
     * @var DOMDocument
     */
    protected $document;

    /**
     * Construct from path to PDepend XML file
     *
     * @param string $path
     * @return void
     */
    public function __construct( $source, CodeProcessorFactory $factory )
    {
        $this->source  = file_get_contents( $source );
        $this->factory = $factory;
    }

    /**
     * Load summary.xml file
     *
     * @param string $path
     * @return void
     */
    public function load( $path )
    {
        $this->document = new \DOMDocument();
        $this->document->load( $path );
    }

    /**
     * Get annotations
     *
     * @return array
     */
    public function getAnnotations()
    {
        $xpath = new \DOMXPath( $this->document );

        $annotations = array();
        foreach ( $xpath->query( '//class' ) as $classNode )
        {
            $files = $classNode->getElementsByTagName( 'file' );
            if ( $files->length === 0 )
            {
                continue;
            }
            $file      = $files->item( 0 )->getAttribute( 'name' );
            $processor = $this->factory->factory( $this->source . $file );

            $metrics = $this->getClassMetrics( $classNode );
            foreach ( $metrics as $metric => $value )
            {
                $class = 'warning';
                if ( ( $value > $this->classTresholds['warning'][$metric] ) ||
                     ( (int) ( $class = 'error' ) ) ||
                     ( $value > $this->classTresholds['error'][$metric] ) )
                {
                    $annotations[] = new Struct\Annotation(
                        $file,
                        $processor->getLineForEntity( $classNode->getAttribute( 'name' ), 'class' ),
                        null,
                        'pdepend',
                        $class,
                        $this->classMetrics[$metric] . ': ' . $value
                    );
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
                        $annotations[] = new Struct\Annotation(
                            $file,
                            $processor->getLineForEntity( $methodNode->getAttribute( 'name' ), 'class' ),
                            null,
                            'pdepend',
                            $class,
                            $this->methodMetrics[$metric] . ': ' . $value
                        );
                    }
                }
            }
        }

        return $annotations;
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
     * Get class metric tag cloud data
     *
     * @param string $selected
     * @return void
     */
    public function getClassesMetric( $selected, $count = 50 )
    {
        $xpath   = new \DOMXPath( $this->document );
        $classes = array();
        $max     = 0;
        foreach ( $xpath->query( '//class' ) as $element )
        {
            $files = $element->getElementsByTagName( 'file' );
            if ( $files->length === 0 )
            {
                continue;
            }
            $file      = $files->item( 0 )->getAttribute( 'name' );
            $processor = $this->factory->factory( $this->source . $file );

            $class   = $element->getAttribute( 'name' );
            $metrics = $this->getClassMetrics( $element );
            $classes[$class]['value'] = $metrics[$selected];
            $classes[$class]['file']  = $file;
            $classes[$class]['line']  = $processor->getLineForEntity( $class, 'class' );

            $max = max( $max, $metrics[$selected] );
        }

        $classes = $this->limitItemList( $classes, $count );
        ksort( $classes );

        return array(
            'name'     => $this->classMetrics[$selected],
            'selected' => $selected,
            'max'      => $max,
            'items'    => $classes,
        );
    }

    /**
     * Get list of available class metrics
     *
     * @return array
     */
    public function getClassMetricList()
    {
        return $this->classMetrics;
    }

    /**
     * Get method metric tag cloud data
     *
     * @param string $selected
     * @param int $count
     * @return void
     */
    public function getMethodsMetric( $selected, $count = 50 )
    {
        $xpath   = new \DOMXPath( $this->document );
        $methods = array();
        $max     = 0;
        foreach ( $xpath->query( '//class' ) as $element )
        {
            $className = $element->getAttribute( 'name' );
            $files     = $element->getElementsByTagName( 'file' );
            if ( $files->length === 0 )
            {
                continue;
            }
            $file      = $files->item( 0 )->getAttribute( 'name' );
            $processor = $this->factory->factory( $this->source . $file );

            foreach ( $element->getElementsByTagName( 'method' ) as $methodElement )
            {
                $method  = $className . '::' . $methodElement->getAttribute( 'name' );
                $metrics = $this->getMethodMetrics( $methodElement );
                $methods[$method]['value'] = $metrics[$selected];
                $methods[$method]['file']  = $file;
                $methods[$method]['line']  = $processor->getLineForEntity( $methodElement->getAttribute( 'name' ), 'function' );

                $max = max( $max, $metrics[$selected] );
            }
        }

        $methods = $this->limitItemList( $methods, $count );
        ksort( $methods );

        return array(
            'name'     => $this->methodMetrics[$selected],
            'selected' => $selected,
            'max'      => $max,
            'items'    => $methods,
        );
    }

    /**
     * Get list of available method metrics
     *
     * @return array
     */
    public function getMethodMetricList()
    {
        return $this->methodMetrics;
    }

    /**
     * Limit item list
     *
     * @param array $items
     * @param int $count
     * @return array
     */
    public function limitItemList( array $items, $count )
    {
        uasort(
            $items,
            function ( $a, $b )
            {
                $diff = $b['value'] - $a['value'];

                if ( $diff < 0 )
                {
                    return -1;
                }
                elseif ( $diff > 0 )
                {
                    return 1;
                }

                return 0;
            }
        );

        return array_slice( $items, 0, $count );
    }
}

