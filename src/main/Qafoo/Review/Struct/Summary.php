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
 * Summary struct class
 *
 * @version $Revision$
 */
class Summary extends MenuEntry
{
    /**
     * Module identifier
     *
     * @var string
     */
    public $text;

    /**
     * Construct
     *
     * @param string $title
     * @param string $module
     * @param string $text
     * @return void
     */
    public function __construct( $title, $module, $text )
    {
        parent::__construct( $title, $module );
        $this->text = $text;
    }
}

