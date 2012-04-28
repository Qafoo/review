<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Controller;
use Qafoo\Review\Analyzer;
use Qafoo\Review\Struct;
use Qafoo\Review\Displayable;
use Qafoo\RMF;

/**
 * Main review controller
 *
 * @version $Revision$
 */
class Review
{
    /**
     * Analyzers utilized
     *
     * @var Analyzer[]
     */
    protected $analyzers;

    /**
     * Construct from analyzers
     *
     * @param Analyzer[] $analyzers
     * @return void
     */
    public function __construct( array $analyzers = array() )
    {
        foreach ( $analyzers as $id => $analyzer )
        {
            $this->addAnalyzer( $id, $analyzer );
        }
    }

    /**
     * Add anaylzer
     *
     * @param string $id
     * @param Analyzer $analyzer
     * @return void
     */
    public function addAnalyzer( $id, Analyzer $analyzer )
    {
        $this->analyzers[$id] = $analyzer;
    }

    /**
     * Analyze src dir
     *
     * @param string $path
     * @return void
     */
    public function analyze( $path )
    {
        foreach ( $this->analyzers as $analyzer )
        {
            $analyzer->analyze( $path );
        }
    }

    /**
     * Show project overview
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function showOverview( RMF\Request $request )
    {
        $analyzers = array();
        $summaries = array();
        foreach ( $this->analyzers as $id => $analyzer )
        {
            if ( $analyzer instanceof Displayable )
            {
                $analyzers[] = $entry = $analyzer->getMenuEntry();
                $entry->module = $id;
            }

            $summaries[] = $summary = $analyzer->getSummary();
            $summary->module = $id;
        }

        return new Struct\Response(
            'overview.twig',
            array(
                'navigation' => $analyzers,
                'summaries'  => $summaries,
            )
        );
    }
}

