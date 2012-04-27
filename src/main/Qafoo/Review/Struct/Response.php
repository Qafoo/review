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
 * Base MySQLi connection class
 *
 * @version $Revision$
 */
class Response extends Struct
{
    /**
     * Name of template to use
     *
     * @var string
     */
    public $template = 'index.twig';

    /**
     * Template data
     *
     * @var array
     */
    public $data;

    /**
     * Construct from template name and optional data
     *
     * @param string $template
     * @param array $data
     * @return void
     */
    public function __construct( $template, array $data = array() )
    {
        $this->template = $template;
        $this->data     = $data;
    }
}

