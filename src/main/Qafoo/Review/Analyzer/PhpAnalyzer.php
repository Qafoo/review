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
 * PhpAnalyzer Analyzer
 *
 * @version $Revision$
 * @license APGLv3
 */
class PhpAnalyzer extends Analyzer implements Displayable
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

    public function analyze( $path )
    {
        $process = new \SystemProcess\SystemProcess( 'phpalizer' );
        $process
            ->argument( 'run' )
            ->argument( '--format' )
            ->argument( 'xml' )
            ->argument( '--output-file' )
            ->argument( $this->resultDir . '/analyzer.xml' )
            ->argument( $path )
        ;

        $process->execute();

        $this->processAnnotations();
    }

    /**
     * Parse analyzer.xml and return list of annotations
     *
     * @return array<string><Struct\Annotation>
     */
    private function parseComments()
    {
        $doc = new \DOMDocument();
        $doc->load( $this->resultDir . '/analyzer.xml' );
        $xpath = new \DOMXPath( $doc );

        $annotations = array();
        foreach ( $xpath->query( '//file' ) as $fileNode )
        {
            $path = $fileNode->getElementsByTagName( 'path' )->item(0)->textContent;

            foreach ( $fileNode->getElementsByTagName( 'comment' ) as $commentNode )
            {
                if ($commentNode->getElementsByTagName('comment')->length > 0) {
                    $line = $commentNode->getAttribute('line');
                    continue;
                }

                $message = $commentNode->getElementsByTagName('message')->item(0)->textContent;

                foreach ($commentNode->getElementsByTagName( 'param' ) as $paramNode)
                {
                    $name = '%' . $paramNode->getAttribute( 'name' ) . '%';
                    $value = $paramNode->textContent;

                    $message = str_replace( $name, $value, $message );
                }

                $annotations[$path][] = new Struct\Annotation(
                    $path,
                    (int) $line,
                    null,
                    'php-analyzer',
                    'warning',
                    $message
                );
            }
        }

        return $annotations;
    }

    /**
     * Process annotations from summary XML file
     *
     * @param string $path
     * @param string $summaryXml
     * @return void
     */
    protected function processAnnotations()
    {
        $annotations = $this->parseComments();

        foreach ($annotations as $file => $fileAnnotations) {
            foreach ($fileAnnotations as $annotation) {
                $this->gateway->create($annotation);
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
            'PHP Analyzer',
            'PHP Analyzer is a control and data flow analyzer and type-inference engine.'
        );
    }

    /**
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable()
    {
        return is_file( $this->resultDir . '/analyzer.xml' );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'PHP Analyzer' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {
        if ( !is_file( $this->resultDir . '/analyzer.xml' ) )
        {
            return new Struct\Response(
                'not_available.twig',
                array(
                    'summary'  => $this->getSummary(),
                )
            );
        }

        $doc = new \DOMDocument();
        $doc->load( $this->resultDir . '/analyzer.xml' );
        $xpath = new \DOMXPath( $doc );

        $annotations = $this->parseComments();

        return new Struct\Response(
            'phpanalyzer.twig',
            array(
                'annotations'  => $annotations,
            )
        );
    }
}
