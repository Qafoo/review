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

/**
 * PDepend analyzer class
 *
 * @version $Revision$
 */
class PDepend extends Analyzer
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
        $process = new \SystemProcess\SystemProcess( 'pdepend' );
        $process->nonZeroExitCodeException = true;
        $process
            ->argument( '--jdepend-chart=' . $this->resultDir . '/pdepend_jdepend.svg' )
            ->argument( '--overview-pyramid=' . $this->resultDir . '/pdepend_pyramid.svg' )
            ->argument( '--arbit-xml=' . $this->resultDir . '/pdepend_summary.xml' )
            ->argument( $path );

        $process->execute();
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {

    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {

    }
}

