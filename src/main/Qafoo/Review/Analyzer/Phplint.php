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
 * Phplint analyzer class
 *
 * @version $Revision$
 */
class Phplint extends Analyzer implements Displayable
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
        $process = new \SystemProcess\SystemProcess( 'phplint' );
        $process
            ->argument( '--format=checkstyle' )
            ->argument( '--output=' . $this->resultDir . '/phplint.xml' )
            ->argument( $path );

        $process->execute();

        $this->processAnnotations( $path, $this->resultDir . '/phplint.xml' );
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
                    'phplint',
                    $violation->getAttribute( 'severity' ),
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
            'PHPLint',
            'PHPLint tries to locate usage of the bad parts of PHP.'
        );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'PHPLint' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        $doc = new \DOMDocument();
        $doc->load( $this->resultDir . '/phplint.xml' );
        $xpath = new \DOMXPath( $doc );

        $annotations = array();
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            foreach ( $fileNode->getElementsByTagName( 'error' ) as $violation )
            {
                $annotations[$fileNode->getAttribute( 'name' )][] = new Struct\Annotation(
                    $fileNode->getAttribute( 'name' ),
                    (int) $violation->getAttribute( 'line' ),
                    null,
                    'phplint',
                    $violation->getAttribute( 'severity' ),
                    $violation->getAttribute( 'message' )
                );
            }
        }

        return new Struct\Response(
            'phplint.twig',
            array(
                'annotations'  => $annotations,
            )
        );
    }
}

