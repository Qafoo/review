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
use Qafoo\Review\Analyzer;
use Qafoo\Review\Struct;
use Qafoo\Review\Displayable;
use Qafoo\RMF;

/**
 * Controller delivering static files for internal PHP webserver
 *
 * @TODO: Clean this code up and integrate with response mechanism.
 *
 * @version $Revision$
 * @license APGLv3
 */
class Asset
{
    /**
     * Path to htdocs/
     *
     * @var string
     */
    protected $basePath;

    /**
     * File extension to mime type mapping
     *
     * @var array
     */
    protected $mimeTypes = array(
        'js'  => 'text/javascript',
        'css' => 'text/css',
        'png' => 'image/png',
    );

    /**
     * Construct from base path
     *
     * @param mixed $basePath
     * @return void
     */
    public function __construct( $basePath )
    {
        $this->basePath = $basePath;
    }

    /**
     * Show analyzer results
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function show( RMF\Request $request )
    {
        if ( !is_file( $path = $this->basePath . $request->path ) )
        {
            header( "Status: 404 Not Found" );
            exit();
        }

        $type = strtolower( pathinfo( $path, \PATHINFO_EXTENSION ) );
        if ( !isset( $this->mimeTypes[$type] ) )
        {
            throw new \RuntimeException( "Unknown file type." );
        }

        header( "Content-Type: " . $this->mimeTypes[$type] );
        readfile( $path );
        exit();
    }
}

