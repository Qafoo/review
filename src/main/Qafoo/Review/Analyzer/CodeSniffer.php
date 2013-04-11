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
 * Phpmd analyzer class
 *
 * @version $Revision$
 * @license APGLv3
 */
class CodeSniffer extends Analyzer implements Displayable
{
    /**
     * Result directory
     *
     * @var string
     */
    protected $resultDir;

    /**
     * Result XML file location
     *
     * @var string
     */
    protected $resultFile;

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
        $this->resultFile = $resultDir . '/phpcs.xml';
    }

    /**
     * Analyze source
     *
     * @param string $path
     * @return void
     */
    public function analyze( $path )
    {
        $process = new \SystemProcess\SystemProcess( 'phpcs' );
        $process
            ->argument( '--standard=src/config/code_sniffer_rules.xml' )
            ->argument( '--report=xml' )
            ->argument( '--report-file=' . $this->resultFile )
            ->argument( $path );

        $process->execute();

        $this->processAnnotations( $path, $this->resultFile );
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

        // Replace all paths in summary.xml with relative paths
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            $fileNode->setAttribute( 'name', str_replace( $path, '', $fileNode->getAttribute( 'name' ) ) );
        }
        $doc->save( $summaryXml );

        foreach ($this->extractAnnotations($xpath) as $annotation)
        {
            $this->gateway->create( $annotation );
        }
    }

    /**
     * Extracts annotations from the XML processed with $xpath
     *
     * @param mixed $xpath
     * @return array(Struct\Annotation)
     */
    private function extractAnnotations( $xpath )
    {
        $annotations = array();

        // Find all metrics and emit signals for warnings and errors
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            $annotations = array_merge(
                $annotations,
                $this->createViolations( $fileNode, $fileNode->getElementsByTagName( 'error' ) ),
                $this->createViolations( $fileNode, $fileNode->getElementsByTagName( 'warning' ) )
            );
        }

        return $annotations;
    }

    /**
     * Creates annotations for $fileNode from $violationNodes
     *
     * @param \DOMElement $fileNode
     * @param \DOMNodeList $violationNodes
     * @return array(Struct\Annotation)
     */
    private function createViolations( \DOMElement $fileNode, \DOMNodeList $violationNodes )
    {
        $annotations = array();
        foreach ( $violationNodes as $violation )
        {
            $annotations[] = new Struct\Annotation(
                $fileNode->getAttribute( 'name' ),
                (int)$violation->getAttribute( 'line' ),
                (int)$violation->getAttribute( 'column' ),
                'phpcs',
                $violation->localName,
                $violation->textContent
            );
        }
        return $annotations;
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'PHP_CodeSniffer',
            'PHP_CodeSniffer tokenises PHP, JavaScript and CSS files and detects violations of a defined set of coding standards.'
        );
    }

    /**
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable()
    {
        return is_file( $this->resultFile );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'PHPCS' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        if ( !is_file( $this->resultFile ) )
        {
            return new Struct\Response(
                'not_available.twig',
                array(
                    'summary'  => $this->getSummary(),
                )
            );
        }

        $doc = new \DOMDocument();
        $doc->load( $this->resultFile );
        $xpath = new \DOMXPath( $doc );

        $annotations = array();
        foreach ( $this->extractAnnotations( $xpath ) as $annotation )
        {
            $annotations[$annotation->file][] = $annotation;
        }

        return new Struct\Response(
            'phpmd.twig',
            array(
                'annotations'  => $annotations,
            )
        );
    }
}

