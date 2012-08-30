<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Struct;
use Qafoo\Review\Struct;

/**
 * Menu entry struct class
 *
 * @version $Revision$
 * @license APGLv3
 */
class MenuEntry extends Struct
{
    /**
     * Title of menu entry
     *
     * @var string
     */
    public $title;

    /**
     * Module identifier
     *
     * @var string
     */
    public $module;

    /**
     * Construct
     *
     * @param string $title
     * @return void
     */
    public function __construct( $title )
    {
        $this->title = $title;
    }
}

