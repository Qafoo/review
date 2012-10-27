<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\CodeProcessor;

use Qafoo\Review\CodeProcessor;

/**
 * Return highlighted PHP code
 *
 * @version $Revision$
 * @license APGLv3
 */
class Php extends CodeProcessor
{
    /**
     * Path to file
     *
     * @var string
     */
    protected $file;

    /**
     * Mapping of colors to semantic styles
     *
     * @var array
     */
    protected $styleMapping = array(
        '#0000bb' => 'code',
        '#ff8000' => 'comment',
        '#007700' => 'keyword',
        '#000000' => 'html',
        '#dd0000' => 'string',
    );

    /**
     * Local line array cache
     *
     * @var array
     */
    private $lines;

    /**
     * Local index cache
     *
     * @var array
     */
    private $index;

    /**
     * PHP entity types nad regular expressions to find them.
     *
     * @var array
     */
    protected $entities = array(
        'function'  => '(function\\s+(?P<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))',
        'class'     => '(^\\s*(?:abstract\\s+)?class\\s+(?P<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))',
        'interface' => '(interface\\s+(?P<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))',
    );

    /**
     * Load file
     *
     * @param string $file
     * @return void
     */
    public function load( $file )
    {
        $this->file = $file;
    }

    /**
     * Get lines from file
     *
     * @return array
     */
    protected function getLines()
    {
        if ( !$this->lines )
        {
            $content = highlight_file( $this->file, true );

            $content = $this->convertWhitespaces( $content );
            $content = $this->removeBullshitMarkup( $content );
            $content = $this->convertStyles( $content );
            $this->lines = $this->splitLines( $content );
        }

        return $this->lines;
    }

    /**
     * Convert whitespace
     *
     * @param string $content
     * @return string
     */
    protected function convertWhitespaces( $content )
    {
        $content = str_replace( '<br />', "\n", $content );
        $content = str_replace( '&nbsp;', ' ', $content );
        return $content;
    }

    /**
     * Remove bullshit markup
     *
     * @param string $content
     * @return string
     */
    protected function removeBullshitMarkup( $content )
    {
        $content = preg_replace( '(^<code[^>]*>\\s*)', '', $content );
        $content = preg_replace( '(\\s*</code[^>]*>\\s*$)', '', $content );

        $content = preg_replace( '(^<span[^>]*>\\s*)', '', $content );
        $content = preg_replace( '(\\s*</span[^>]*>\\s*$)', '', $content );

        return $content;
    }

    /**
     * Convert styles
     *
     * @param string $content
     * @return string
     */
    protected function convertStyles( $content )
    {
        $styleMapping = $this->styleMapping;
        return preg_replace_callback(
            '(<span[^>]*style="color:\\s*(?P<color>#[A-Fa-f0-9]{3,6})"[^>]*>)',
            function ( $matches ) use ( $styleMapping )
            {
                $color = strtolower( $matches['color'] );
                if ( !isset( $styleMapping[$color] ) )
                {
                    throw new \Exception( "No style available for $color." );
                }

                return sprintf(
                    '<span class="%s">',
                    $styleMapping[$color]
                );
            },
            $content
        );
    }

    /**
     * Split content into lines
     *
     * Ensures tags are properly opened and closed per line.
     *
     * @param string $content
     * @return array
     */
    protected function splitLines( $content )
    {
        $lines = preg_split( '(\r\n|\r|\n)', $content );
        $stack = array();

        foreach ( $lines as $nr => $line )
        {
            $result = '';
            foreach ( $stack as $class )
            {
                $result .= "<span class=\"$class\">";
            }

            preg_match_all( '(<(/?)span\\s*(?:class="([^"]*)")?>)', $line, $matches, \PREG_SET_ORDER );
            foreach ( $matches as $span )
            {
                if ( $span[1] === '/' )
                {
                    array_pop( $stack );
                }
                else
                {
                    array_push( $stack, $span[2] );
                }
            }

            $result .= rtrim( $line );
            foreach ( $stack as $class )
            {
                $result .= '</span>';
            }

            $lines[$nr] = $result;
        }

        return $lines;
    }

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
    public function getSourceData()
    {
        $content = $this->getLines();

        $lines = array();
        foreach ( $content as $nr => $line )
        {
            $annotations    = isset( $this->annotations[$nr] ) ? $this->annotations[$nr] : array();
            $lines[$nr + 1] = array(
                'content' => $line,
                'annotations' => $annotations,
                'messages'    => array_filter(
                    $annotations,
                    function ( $annotation )
                    {
                        return strlen( $annotation->message );
                    }
                ),
            );
        }

        return $lines;
    }

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
    public function getIndex()
    {
        if ( $this->index !== null )
        {
            return $this->index;
        }

        $this->index = array();

        $content = $this->getLines();
        foreach ( $content as $nr => $line )
        {
            foreach ( $this->entities as $name => $expression )
            {
                if ( preg_match( $expression, strip_tags( $line ), $match ) )
                {
                    $this->index[] = array(
                        'type' => $name,
                        'line' => $nr + 1,
                        'name' => $match['name'],
                    );
                }
            }
        }

        return $this->index;
    }

    /**
     * Get line for entity
     *
     * Get the line in the source code file,where the specified entity is
     * located.
     *
     * By default only a name of the entity is required, this could, for
     * example, be a function or class name. Optionally a type may be passed.
     * The type can be something like "class", "function" or whatever makes
     * sense in the given source file.
     *
     * @param string $name
     * @param string $type
     * @return int
     */
    public function getLineForEntity( $name, $type = null )
    {
        foreach ( $this->getIndex() as $values )
        {
            if ( $values['name'] === $name )
            {
                return $values['line'];
            }
        }

        return 1;
    }
}

