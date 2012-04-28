<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Struct;
use Qafoo\Review\Struct;

/**
 * Menu entry struct class
 *
 * @version $Revision$
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
     * @param string $module
     * @return void
     */
    public function __construct( $title, $module )
    {
        $this->title  = $title;
        $this->module = $module;
    }
}

