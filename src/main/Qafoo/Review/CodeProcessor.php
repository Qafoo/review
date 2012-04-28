<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;

/**
 * Return highlighted code
 *
 * @version $Revision$
 */
class CodeProcessor
{
    /**
     * Contents of highlighted file
     *
     * @var string
     */
    protected $contents;

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
     * Load file
     *
     * @param string $file
     * @return void
     */
    public function load( $file )
    {
        $this->content = highlight_file( $file, true );

        $this->content = $this->convertWhitespaces( $this->content );
        $this->content = $this->removeBullshitMarkup( $this->content );
        $this->content = $this->convertStyles( $this->content );
        $this->content = $this->splitLines( $this->content );
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
     * Add annotations to source code
     *
     * @param Struct\Annotation[] $annotations
     * @return void
     */
    public function addAnnotations( array $annotations )
    {
        // @TODO: Implement
    }

    /**
     * Get highlighted file as HTML
     *
     * @return string
     */
    public function getHtml()
    {
        $html = "";

        foreach ( $this->content as $nr => $line )
        {
            $html .= sprintf( '<li id="line_%d"><pre><a href="#line_%d">%s</a></pre></li>' . "\n",
                $nr + 1,
                $nr + 1,
                $line
            );
        }

        return $html;
    }
}

