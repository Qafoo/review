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
use Qafoo\RMF;

/**
 * Phpcpd analyzer class
 *
 * @version $Revision$
 * @license APGLv3
 */
class Source extends Analyzer
{
    /**
     * Result directory
     *
     * @var string
     */
    protected $resultDir;

    /**
     * Source directory
     *
     * @var string
     */
    protected $sourceDir;

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
        $this->sourceDir = $resultDir . '/code/';
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
        if (!is_dir($this->sourceDir)) {
            mkdir($this->sourceDir, 0777, true);
        }

        file_put_contents(
            $this->resultDir . '/source_tree.js',
            json_encode($this->scanPath($path))
        );
    }

    /**
     * Scan path and list its contents
     *
     * @param string $path
     * @return array
     */
    protected function scanPath($path, $localPath = array())
    {
        $contents = array();

        foreach (glob($path . '/*') as $item) {
            $localName = basename($item);
            $newLocalPath = array_merge($localPath, array($localName));
            if (is_dir($item)) {
                $contents[] = array(
                    'name' => $localName,
                    'type' => 'inode/directory',
                    'path' => $newLocalPath,
                    'content' => null,
                    'children' => $this->scanPath($item, $newLocalPath),
                );
            } else {
                $newPath = $this->sourceDir . '/' . md5($item) . '.txt';
                copy($item, $newPath);
                $contents[] = array(
                    'name' => $localName,
                    'type' => mime_content_type($item),
                    'path' => $newLocalPath,
                    'content' => str_replace($this->resultDir, '', $newPath),
                    'children' => array(),
                );
            }
        }

        return $contents;
    }

    /**
     * Get summary
     *
     * @return Struct\Summary
     */
    public function getSummary()
    {
        return new Struct\Summary(
            'Source listing',
            'Builds a list of the projects source'
        );
    }
}

