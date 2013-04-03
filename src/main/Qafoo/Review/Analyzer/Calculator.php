<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
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
 * @license APGLv3
 */
class Calculator extends Analyzer implements Displayable
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
     * Model to handle summary.xml
     *
     * @var PDepend\Model
     */
    protected $model;

    /**
     * Create from annotation gateway
     *
     * @param string $resultDir
     * @param AnnotationGateway $gateway
     * @param Calculator\Model $model
     * @return void
     */
    public function __construct( $resultDir, AnnotationGateway $gateway, Calculator\Model $model )
    {
        $this->resultDir = $resultDir;
        $this->gateway   = $gateway;
        $this->model     = $model;
    }

    /**
     * Analyze source
     *
     * @param string $path
     * @return void
     */
    public function analyze( $path )
    {
        // Just depend on the PDepend generated stuff
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'Calculator',
            'Calculate aggregate metrics based on a provided formula.'
        );
    }

    /**
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable()
    {
        return is_file( $this->resultDir . '/pdepend_summary.xml' );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'Calculator' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        if ( !is_file( $this->resultDir . '/pdepend_summary.xml' ) )
        {
            return new Struct\Response(
                'not_available.twig',
                array(
                    'summary'  => $this->getSummary(),
                )
            );
        }

        $formula = isset( $request->variables['formula'] ) ? $request->variables['formula'] : '$ce / ( $ca + $ce )';
        $this->model->load( $this->resultDir . '/pdepend_summary.xml' );
        $classes = $this->model->calculateTopClasses( $formula, 25 );
        $methods = $this->model->calculateTopMethods( $formula, 25 );

        return new Struct\Response(
            'calculator.twig',
            array(
                'formula'       => $formula,
                'classes'       => $classes,
                'classMetrics'  => $this->model->getClassMetricList(),
                'methods'       => $methods,
                'methodMetrics' => $this->model->getMethodMetricList(),
            )
        );
    }
}

