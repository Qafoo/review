<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;
use Qafoo\RMF;

/**
 * Interface for front-end displayable items
 *
 * @version $Revision$
 */
interface Displayable
{
    /**
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable();

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry();

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request );
}

