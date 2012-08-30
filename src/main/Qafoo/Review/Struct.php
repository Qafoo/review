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
 * Base struct class
 *
 * @version $Revision$
 * @license APGLv3
 */
abstract class Struct
{
    /**
     * Creates the struct optionally with the given values.
     *
     * @param array $record
     */
    public function __construct( array $record = array() )
    {
        foreach ( $record as $name => $value )
        {
            if ( property_exists( $this, $name ) )
            {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * Disable read access to unknown prioperties
     *
     * @param string $property
     * @return mixed
     */
    public function __get( $property )
    {
        throw new ValueException( 'Trying to get non-existing property ' . $property );
    }

    /**
     * Disable set access to unknwon properties
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        throw new ValueException( 'Trying to set non-existing property ' . $property );
    }

    /**
     * Default clone method
     *
     * @return void
     */
    public function __clone()
    {
        foreach ( $this as $property => $value )
        {
            if ( is_object( $value ) )
            {
                $this->$property = clone $value;
            }
        }
    }

    /**
     * Default recreation method from var_export
     *
     * @param array $properties
     * @return Struct
     */
    public static function __set_state( array $properties )
    {
        $struct = new static();
        foreach ( $properties as $property => $value )
        {
            $struct->$property = $value;
        }

        return $struct;
    }
}

