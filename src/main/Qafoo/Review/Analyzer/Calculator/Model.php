<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Analyzer\Calculator;
use Qafoo\Review\Analyzer\PDepend;
use Qafoo\Review\Struct;

/**
 * PDepend analyzer class
 *
 * @version $Revision$
 * @license APGLv3
 */
class Model extends PDepend\Model
{
    public function calculateTop( $formula, $count )
    {
        $xpath   = new \DOMXPath( $this->document );
        $classes = array();
        foreach ( $xpath->query( '//class' ) as $element )
        {
            $files = $element->getElementsByTagName( 'file' );
            if ( $files->length === 0 )
            {
                continue;
            }

            $class   = $element->getAttribute( 'name' );
            $metrics = $this->getClassMetrics( $element );
            $classes[$class]['value'] = $value = $this->evaluate( $formula, $metrics );
            $classes[$class]['file']  = $files->item( 0 )->getAttribute( 'name' );
            $classes[$class]['line']  = $element->getAttribute( 'line' );
        }

        return $this->limitItemList( $classes, $count );
    }

    protected function evaluate( $formula, array $values )
    {
        // Oh yeah, this is pure EVIL! (but simple :)
        extract( $values );
        @eval( "\$result = $formula;" );

        if ( !isset( $result ) )
        {
            throw new \RuntimeException( "Could not interprete formula: $formula" );
        }

        return $result;
    }
}

