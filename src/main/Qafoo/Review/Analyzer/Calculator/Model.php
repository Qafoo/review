<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Analyzer\Calculator;
use Qafoo\Review\Analyzer\PDepend;
use Qafoo\Review\Struct;

/**
 * PDepend analyzer class
 *
 * @version $Revision$
 */
class Model extends PDepend\Model
{
    public function calculateTop( $formula, $count )
    {
        $doc = new \DOMDocument();
        $doc->load( $this->path );

        $xpath   = new \DOMXPath( $doc );
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
            $classes[$class]['line']  = $element->getAttribute( 'startLine' );
        }

        return $this->limitItemList( $classes, $count );
    }

    protected function evaluate( $formula, array $values )
    {
        // Oh yeah, this is pure EVIL! (but simple :)
        extract( $values );
        @eval( "\$result = $formula;" );
        return $result;
    }
}

