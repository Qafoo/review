<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Controller;
use Qafoo\Review\Analyzer;

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
        foreach ( $analyzers as $analyzer )
        {
            $this->addAnalyzer( $analyzer );
        }
    }

    /**
     * Add anaylzer
     *
     * @param Analyzer $analyzer
     * @return void
     */
    public function addAnalyzer( Analyzer $analyzer )
    {
        $this->analyzers[] = $analyzer;
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
}

