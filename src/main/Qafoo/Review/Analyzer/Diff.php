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
 * Phpmd analyzer class
 *
 * @version $Revision$
 */
class Diff extends Analyzer implements Displayable
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
     * @param string $oldPath
     * @return void
     */
    public function analyze( $path, $oldPath = null )
    {
        if ( !$oldPath )
        {
            return;
        }

        $process = new \SystemProcess\SystemProcess( 'diff' );
        $process
            ->argument( '-rEbBiNd' )
            ->argument( $oldPath )
            ->argument( $path );

        $process->execute();
        file_put_contents( $this->resultDir . '/diff.patch', $process->stdoutOutput );

        $this->processAnnotations( $path, $this->resultDir . '/diff.patch' );
    }

    /**
     * Process annotations from summary XML file
     *
     * @param string $path
     * @param string $diff
     * @return void
     */
    protected function processAnnotations( $path, $diff )
    {
        $diffPartRegexp = '
            (?P<start>\\d+(?:,\\d+)?)
            (?P<type>[acd])
            (?P<end>\\d+(?:,\\d+)?)\\n
            (?P<removed>(?:<\\s.*\\n)+)?
            (?:---\\n)?
            (?P<added>(?:>\\s.*\\n)+)?
        ';
        $diffContent = file_get_contents( $diff );
        $diffContent = preg_replace( '(\\r\\n|\\r|\\n)', "\n", $diffContent );
        while ( preg_match( '(\\A
                    (?:Binary\\s+files[^\\n]*\\n)*
                    diff .*? (?P<file>[^\\s]+)\\n
                    (?P<diff>(?:' . $diffPartRegexp .  ')+)
                    (?:\\\\[^\\n]*\\n)?
                )x', $diffContent, $match ) )
        {
            $file     = str_replace( $path . '/', '', $match['file'] );
            $fileDiff = $match['diff'];

            $diffContent = substr( $diffContent, strlen( $match[0] ) );

            while ( preg_match( '(\\A' . $diffPartRegexp . ')x', $fileDiff, $match ) )
            {
                $range = explode( ',', $match['end'] );
                $range = count( $range ) > 1 ? range( $range[0], $range[1] ) : $range;

                switch ( $match['type'] )
                {
                    case 'a':
                        foreach ( $range as $line )
                        {
                            $this->gateway->create( new Struct\Annotation( $file, $line, null, 'diff', 'added' ) );
                        }
                        break;
                    case 'c':
                        foreach ( $range as $line )
                        {
                            $this->gateway->create( new Struct\Annotation( $file, $line, null, 'diff', 'added' ) );
                        }
                        break;
                }

                $fileDiff = substr( $fileDiff, strlen( $match[0] ) );
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
            'Diff',
            'Shows the differences between the current and an older version of the source code.'
        );
    }

    /**
     * Get menu entry
     *
     * @return Struct\MenuEntry
     */
    public function getMenuEntry()
    {
        return new Struct\MenuEntry( 'Diff' );
    }

    /**
     * Render yourself
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function render( RMF\Request $request )
    {

        return new Struct\Response(
            'diff.twig',
            array(
            )
        );
    }
}

