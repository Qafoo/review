<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Controller;
use Qafoo\Review\AnnotationGateway;
use Qafoo\Review\CodeProcessor;
use Qafoo\Review\Struct;
use Qafoo\RMF;

/**
 * Main source controller
 *
 * @version $Revision$
 */
class Source
{
    /**
     * Annotation gateway
     *
     * @var AnnotationGateway
     */
    protected $gateway;

    /**
     * Source dir
     *
     * @var string
     */
    protected $source;

    /**
     * Construct from analyzers
     *
     * @param string $source
     * @param AnnotationGateway $gateway
     * @return void
     */
    public function __construct( $source, AnnotationGateway $gateway )
    {
        $this->source  = file_get_contents( $source );
        $this->gateway = $gateway;
    }

    /**
     * Show project overview
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function show( RMF\Request $request )
    {
        $path = $request->variables['path'] ?: '/';

        $source      = array();
        $annotations = array();
        if ( file_exists( $file = $this->source . '/' . $path ) &&
             is_file( $file ) )
        {
            $processor = new CodeProcessor();
            $processor->load( $file );
            $processor->addAnnotations(
                $annotations = $this->gateway->getAnnotationsForFile( $path )
            );
            $source = $processor->getSourceData();
        }

        return new Struct\Response(
            'source.twig',
            array(
                'path'        => $path,
                'tree'        => $this->getSourceTree( $path ),
                'source'      => $source,
                'annotations' => $annotations,
            )
        );
    }

    /**
     * Get source tree
     *
     * @param string $path
     * @return void
     */
    protected function getSourceTree( $path )
    {
        $tree = new \RecursiveDirectoryIterator(
            $this->source,
            \FilesystemIterator::CURRENT_AS_FILEINFO |
            \FilesystemIterator::KEY_AS_PATHNAME |
            \FilesystemIterator::SKIP_DOTS |
            \FilesystemIterator::UNIX_PATHS
        );

        return $this->toArray( $tree, $path );
    }

    /**
     * Recursively convert RecursiveDirectoryIterator to array
     *
     * @param \RecursiveDirectoryIterator $tree
     * @param string $path
     * @return array
     */
    protected function toArray( \RecursiveDirectoryIterator $tree, $path )
    {
        $array = array();
        $types = array();
        $names = array();
        foreach ( $tree as $file )
        {
            if ( strpos( $file->getFileName(), '.' ) === 0 )
            {
                continue;
            }

            $types[] = (int) $file->isDir();
            $names[] = $file->getFileName();
            $array[] = $entry = array(
                'type'     => $file->isDir() ? 'folder' : 'file',
                'name'     => $file->getFileName(),
                'path'     => $localPath = str_replace( $this->source, '', $file->getPath() . '/' . $file->getFileName() ),
                'children' => $tree->hasChildren() ? $this->toArray( $tree->getChildren(), $path ) : array(),
                'state'    => strpos( $path, $localPath ) === 0 ? 'open' : 'close',
            );
        }

        array_multisort(
            $types, SORT_NUMERIC, SORT_DESC,
            $names, SORT_STRING, SORT_ASC,
            $array
        );

        return $array;
    }
}

