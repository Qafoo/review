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
     * Get menu entries
     *
     * @return array
     */
    protected function getMenuEntries()
    {
        $analyzers = array();
        foreach ( $this->analyzers as $id => $analyzer )
        {
            if ( $analyzer instanceof Displayable )
            {
                $analyzers[] = $entry = $analyzer->getMenuEntry();
                $entry->module = $id;
            }
        }

        return $analyzers;
    }

    /**
     * Show project overview
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function showOverview( RMF\Request $request )
    {
        $summaries = array();
        foreach ( $this->analyzers as $id => $analyzer )
        {
            $summaries[] = $summary = $analyzer->getSummary();
            $summary->module = $id;
        }

        return new Struct\Response(
            'overview.twig',
            array(
                'navigation' => $this->getMenuEntries(),
                'summaries'  => $summaries,
            )
        );
    }

    /**
     * Show analyzer results
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function showAnalyzer( RMF\Request $request )
    {
        if ( !isset( $request->variables['analyzer'] ) ||
             !isset( $this->analyzers[$request->variables['analyzer']] ) )
        {
            throw new \Exception( "Unknown / unspecified analyzer." );
        }

        $response = $this->analyzers[$request->variables['analyzer']]->render( $request );
        $response->data['navigation'] = $this->getMenuEntries();

        return $response;
    }
}

