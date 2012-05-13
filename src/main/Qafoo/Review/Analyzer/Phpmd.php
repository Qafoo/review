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
 * Phpmd analyzer class
 *
 * @version $Revision$
 */
class Phpmd extends Analyzer implements Displayable
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
        $process = new \SystemProcess\SystemProcess( 'phpmd' );
        $process
            ->argument( $path )
            ->argument( 'xml' )
            ->argument( 'codesize,unusedcode,naming' )
            ->argument( '--reportfile' )
            ->argument( $this->resultDir . '/phpmd.xml' );

        $process->execute();

        $this->processAnnotations( $path, $this->resultDir . '/phpmd.xml' );
    }

    /**
     * Process annotations from summary XML file
     *
     * @param string $path
     * @param string $summaryXml
     * @return void
     */
    protected function processAnnotations( $path, $summaryXml )
    {
        $doc = new \DOMDocument();
        $doc->load( $summaryXml );
        $xpath = new \DOMXPath( $doc );

        // Replace all pathes in summary.xml with relative pathes
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            $fileNode->setAttribute( 'name', str_replace( $path, '', $fileNode->getAttribute( 'name' ) ) );
        }
        $doc->save( $summaryXml );

        // Find all metrics and emit signals for warnings and errors
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            foreach ( $fileNode->getElementsByTagName( 'violation' ) as $violation )
            {
                $this->gateway->create( new Struct\Annotation(
                    $fileNode->getAttribute( 'name' ),
                    (int) $violation->getAttribute( 'beginline' ),
                    null,
                    'phpmd',
                    'warning',
                    $violation->textContent
                ) );
            }
        }
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'PHPMD',
            'PHPMD takes a given PHP source code base and look for several potential problems within that source.'
        );
    }

    /**
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable()
    {
        return is_file( $this->resultDir . '/phpmd.xml' );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'PHPMD' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        if ( !is_file( $this->resultDir . '/phpmd.xml' ) )
        {
            return new Struct\Response(
                'not_available.twig',
                array(
                    'summary'  => $this->getSummary(),
                )
            );
        }

        $doc = new \DOMDocument();
        $doc->load( $this->resultDir . '/phpmd.xml' );
        $xpath = new \DOMXPath( $doc );

        $annotations = array();
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            foreach ( $fileNode->getElementsByTagName( 'violation' ) as $violation )
            {
                $annotations[$fileNode->getAttribute( 'name' )][] = new Struct\Annotation(
                    $fileNode->getAttribute( 'name' ),
                    (int) $violation->getAttribute( 'beginline' ),
                    null,
                    'phpmd',
                    'warning',
                    $violation->textContent
                );
            }
        }

        return new Struct\Response(
            'phpmd.twig',
            array(
                'annotations'  => $annotations,
            )
        );
    }
}

