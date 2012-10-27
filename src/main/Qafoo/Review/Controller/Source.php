<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Controller;
use Qafoo\Review\AnnotationGateway;
use Qafoo\Review\CodeProcessorFactory;
use Qafoo\Review\Struct;
use Qafoo\RMF;

/**
 * Main source controller
 *
 * @version $Revision$
 * @license APGLv3
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
        $this->resultDir = dirname( $source );
        $this->source    = file_get_contents( $source );
        $this->gateway   = $gateway;
    }

    /**
     * Get class meta information
     *
     * Returns class meta information, if is has been made available by the UML 
     * analyzer. Can be used to display class dependencies / relations.
     *
     * @param string $path
     * @return array
     */
    protected function getClassMetainformation( $path )
    {
        if ( !is_file( $this->resultDir . '/classes.php' ) )
        {
            return array();
        }

        $classes = include $this->resultDir . '/classes.php';
        foreach ( $classes as $class => $data )
        {
            $data['extends'] = array_flip( $data['extends'] );
            foreach ( $data['extends'] as $extended => $null )
            {
                if ( isset( $classes[$extended] ) )
                {
                    $classes[$extended]['extendedBy'][$class] = $classes[$class]['file'];
                    $data['extends'][$extended] = $classes[$extended]['file'];
                }
                else
                {
                    $data['extends'][$extended] = false;
                }
            }

            $data['uses'] = array_flip( $data['uses'] );
            foreach ( $data['uses'] as $used => $null )
            {
                if ( isset( $classes[$used] ) )
                {
                    $classes[$used]['usedBy'][$class] = $classes[$class]['file'];
                    $data['uses'][$used] = $classes[$used]['file'];
                }
                else
                {
                    $data['uses'][$used] = false;
                }
            }

            $classes[$class] = $data;
        }

        foreach ( $classes as $class => $data )
        {
            if ( $data['file'] === $path )
            {
                return $data;
            }
        }

        return array();
    }

    /**
     * Annotate source file
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function annotate( RMF\Request $request )
    {
        $default = array(
            'file'      => null,
            'line'      => null,
            'character' => null,
            'type'      => 'user',
            'class'     => 'annotate',
            'message'   => null,
        );

        $data = array_merge( $default, $request->body );
        $this->gateway->create(
            new Struct\Annotation(
                $data['file'],
                $data['line'],
                $data['character'],
                $data['type'],
                $data['class'],
                $data['message']
            )
        );

        return false;
    }

    /**
     * Show project overview
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function show( RMF\Request $request )
    {
        $factory = new CodeProcessorFactory();
        $path = $request->variables['path'] ?: '/';

        $source      = array();
        $annotations = array();
        $index       = array();
        if ( file_exists( $file = $this->source . '/' . $path ) &&
             is_file( $file ) )
        {
            $processor = $factory->factory( $file );
            $processor->addAnnotations(
                $annotations = $this->gateway->getAnnotationsForFile( $path )
            );
            $source = $processor->getSourceData();
            $index  = $processor->getIndex();
        }

        return new Struct\Response(
            'source.twig',
            array(
                'path'         => $path,
                'tree'         => $this->getSourceTree( $path ),
                'source'       => $source,
                'index'        => $index,
                'annotations'  => $annotations,
                'dependencies' => $this->getClassMetainformation( $path ),
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

