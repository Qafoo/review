<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Analyzer;
use Qafoo\Review\Analyzer;
use Qafoo\Review\AnnotationGateway;
use Qafoo\Review\Struct;
use Qafoo\Review\Displayable;
use Qafoo\RMF;

/**
 * Phpcpd analyzer class
 *
 * @version $Revision$
 * @license APGLv3
 */
class Phpcpd extends Analyzer implements Displayable
{
    /**
     * Result directory
     *
     * @var string
     */
    protected $resultDir;

    /**
     * Annotation gateway
     *
     * @var AnnotationGateway
     */
    protected $gateway;

    /**
     * Create from annotation gateway
     *
     * @param string $resultDir
     * @param AnnotationGateway $gateway
     * @return void
     */
    public function __construct( $resultDir, AnnotationGateway $gateway )
    {
        $this->resultDir = $resultDir;
        $this->gateway   = $gateway;
    }

    /**
     * Analyze source
     *
     * @param string $path
     * @return void
     */
    public function analyze( $path )
    {
        $process = new \SystemProcess\SystemProcess( 'phpcpd' );
        $process
            ->argument( '--min-lines' )
            ->argument( 4 )
            ->argument( '--min-tokens' )
            ->argument( 30 )
            ->argument( '--log-pmd' )
            ->argument( $this->resultDir . '/phpcpd.xml' )
            ->argument( $path );

        $process->execute();

        // Fix file names
        $doc = new \DOMDocument();
        $doc->load( $this->resultDir . '/phpcpd.xml' );
        $xpath = new \DOMXPath( $doc );

        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            $fileNode->setAttribute( 'path', str_replace( $path, '', $fileNode->getAttribute( 'path' ) ) );
        }
        $doc->save( $this->resultDir . '/phpcpd.xml' );
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'PHP C&P Detector',
            'PHP C&P Detector tries to find code duplication.'
        );
    }

    /**
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable()
    {
        return is_file( $this->resultDir . '/phpcpd.xml' );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'PHP CPD' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        if ( !is_file( $this->resultDir . '/phpcpd.xml' ) )
        {
            return new Struct\Response(
                'not_available.twig',
                array(
                    'summary'  => $this->getSummary(),
                )
            );
        }

        $doc = new \DOMDocument();
        $doc->load( $this->resultDir . '/phpcpd.xml' );
        $xpath = new \DOMXPath( $doc );

        $resultDir    = $this->resultDir;
        $duplications = array();
        foreach ( $xpath->query( '//duplication' ) as $node )
        {
            $duplications[] = array(
                'files' => array_map(
                    function ( $node ) use ( $resultDir )
                    {
                        return array(
                            'path' => $node->getAttribute( 'path' ),
                            'line' => (int) $node->getAttribute( 'line' ),
                        );
                    },
                    iterator_to_array( $node->getElementsByTagName( 'file' ) )
                ),
                'source' => $node->getElementsByTagName( 'codefragment' )->item( 0 )->textContent,
            );
        }

        return new Struct\Response(
            'phpcpd.twig',
            array(
                'duplications'  => $duplications,
            )
        );
    }
}

