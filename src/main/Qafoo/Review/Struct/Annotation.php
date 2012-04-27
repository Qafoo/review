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
class Annotation extends Struct
{
    /**
     * Annotation ID
     *
     * @var int
     */
    public $id

    /**
     * Name of file
     *
     * @var string
     */
    public $file;

    /**
     * Line
     *
     * @var int
     */
    public $line;

    /**
     * Position in line
     *
     * @var int
     */
    public $character;

    /**
     * Annotation type, usually the tool name the annotation originates from.
     *
     * @var string
     */
    public $type;

    /**
     * Annotation class. Usually one of: info, notice, warning, error
     *
     * @var string
     */
    public $class;

    /**
     * Annotation message
     *
     * @var string
     */
    public $message;
}

