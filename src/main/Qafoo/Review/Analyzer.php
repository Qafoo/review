<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;

/**
 * Base analyzer class
 *
 * @version $Revision$
 */
abstract class Analyzer
{
    /**
     * Analyze source
     *
     * @param string $path
     * @return void
     */
    abstract public function analyze( $path );

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    abstract public function getSummary();

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    abstract public function getMenuEntry();
}

