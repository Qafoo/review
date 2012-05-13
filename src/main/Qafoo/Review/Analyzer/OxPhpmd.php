<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Analyzer;
use Qafoo\Review\Analyzer;
use Qafoo\Review\AnnotationGateway;
use Qafoo\Review\Struct;
use Qafoo\Review\Displayable;
use Qafoo\RMF;

/**
 * Special Oxid Phpmd analyzer class
 *
 * @version $Revision$
 */
class OxPhpmd extends Phpmd implements Displayable
{
    /**
     * Analyze source
     *
     * @param string $path
     * @return void
     */
    public function analyze( $path )
    {
        $process = new \SystemProcess\SystemProcess( 'oxphpmd' );
        $process
            ->argument( $path )
            ->argument( 'xml' )
            ->argument( 'oxid' )
            ->argument( '--reportfile' )
            ->argument( $this->resultDir . '/oxphpmd.xml' );

        $process->execute();

        $this->processAnnotations( $path, $this->resultDir . '/oxphpmd.xml' );
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'Oxid PHPMD',
            'Oxid PHPMD takes a given PHP source code base and looks for violations of the Oxid module certification standards.'
        );
    }

    /**
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable()
    {
        return is_file( $this->resultDir . '/oxphpmd.xml' );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'Oxid PHPMD' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        if ( !is_file( $this->resultDir . '/oxphpmd.xml' ) )
        {
            return new Struct\Response(
                'not_available.twig',
                array(
                    'summary'  => $this->getSummary(),
                )
            );
        }

        $doc = new \DOMDocument();
        $doc->load( $this->resultDir . '/oxphpmd.xml' );
        $xpath = new \DOMXPath( $doc );

        $annotations = array();
        $values = array(
            'CyclomaticComplexity' => 0,
            'Coverage'             => 100,
            'CrapIndex'            => 0,
            'NPathComplexity'      => 0,
        );

        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            foreach ( $fileNode->getElementsByTagName( 'violation' ) as $violation )
            {
                $annotations[$fileNode->getAttribute( 'name' )][] = new Struct\Annotation(
                    $fileNode->getAttribute( 'name' ),
                    (int) $violation->getAttribute( 'beginline' ),
                    null,
                    'oxphpmd',
                    'warning',
                    $violation->textContent
                );

                $value = (float) preg_replace( '(^.*?(\\d+(?:\\.\\d+)?).*$)', '$1', trim( $violation->nodeValue ) );
                switch ( $rule = $violation->getAttribute( 'rule' ) )
                {
                    case 'CyclomaticComplexity':
                    case 'CrapIndex':
                    case 'NPathComplexity':
                        $values[$rule] = max( $values[$rule], $value );
                        break;

                    case 'Coverage':
                        $values[$rule] = min( $values[$rule], $value );
                        break;

                    default:
                        // Ignore other metrics for now.
                }
            }
        }

        $factor =
            max( 1, ( $values['CrapIndex'] - 20 ) / 10 ) *
            max( 1, ( $values['CyclomaticComplexity'] - 3 ) / 1 ) *
            max( 1, ( $values['NPathComplexity'] - 100 ) / 100 );

        return new Struct\Response(
            'oxphpmd.twig',
            array(
                'annotations' => $annotations,
                'metrics'     => $values,
                'costs'       => round( 119 + 200 * $factor, 2 ),
            )
        );
    }
}

