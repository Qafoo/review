<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;

/**
 * Return highlighted code
 *
 * @version $Revision$
 * @license APGLv3
 */
abstract class CodeProcessor
{
    /**
     * Array containing all annotations.
     *
     * Array is maintained as an array array of annotations per line number.
     *
     * @var Struct\Annotation[][]
     */
    protected $annotations;

    /**
     * Add annotations to source code
     *
     * @param Struct\Annotation[] $annotations
     * @return void
     */
    public function addAnnotations( array $annotations )
    {
        foreach ( $annotations as $annotation )
        {
            $this->annotations[$annotation->line - 1][] = $annotation;
        }
    }

    /**
     * Load file
     *
     * @param string $file
     * @return void
     */
    abstract public function load( $file );

    /**
     * Get highlighted file data
     *
     * Return data of the highlighted source code file, line by line.
     *
     * The returned data should have the format:
     *
     * <code>
     *  array(
     *      array(
     *          'content'     => <html>,
     *          // All annotations
     *          'annotations' => Struct\Annotation[],
     *          // Annotations containing messages
     *          'messages'    => Struct\Annotation[],
     *      ),
     *      // …
     *  )
     * </code>
     *
     * @TODO: Return an array of structs here?
     *
     * @return array
     */
    abstract public function getSourceData();

    /**
     * Get index for for file
     *
     * Return data to build up an additional index on the file.
     *
     * The returned data should have the format:
     *
     * <code>
     *  array(
     *      array(
     *          'type' => <string>,
     *          'line' => <int>,
     *          'name' => <string>,
     *      ),
     *      // …
     *  )
     * </code>
     *
     * @TODO: Return an array of structs here?
     *
     * @return array
     */
    abstract public function getIndex();
}

