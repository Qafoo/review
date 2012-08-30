<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;

/**
 * Base analyzer class
 *
 * @version $Revision$
 * @license APGLv3
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
}

