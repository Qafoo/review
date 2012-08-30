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
 * Summary struct class
 *
 * @version $Revision$
 * @license APGLv3
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
     * @param string $text
     * @return void
     */
    public function __construct( $title, $text )
    {
        parent::__construct( $title );
        $this->text = $text;
    }
}

