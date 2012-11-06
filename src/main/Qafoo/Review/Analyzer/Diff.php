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

        $this->parseDiff( $path, $this->resultDir . '/diff.patch' );
        $this->processAnnotations();
    }

    /**
     * Parse diff into a sensible PHP array and store the parsed results
     *
     * @param string $path
     * @param string $diff
     * @return void
     */
    protected function parseDiff( $path, $diff )
    {
        $parsedDiff = array();

        $diffPartRegexp = '
            (?P<lRemoved>\\d+(?:,\\d+)?)
            (?P<type>[acd])
            (?P<lAdded>\\d+(?:,\\d+)?)\\n
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
                $removeRange = explode( ',', $match['lRemoved'] );
                $removeRange = count( $removeRange ) > 1 ? range( $removeRange[0], $removeRange[1] ) : $removeRange;

                $addedRange = explode( ',', $match['lAdded'] );
                $addedRange = count( $addedRange ) > 1 ? range( $addedRange[0], $addedRange[1] ) : $addedRange;

                $parsedDiff[$file][] = array(
                    'type'    => $match['type'],
                    'removed' => $removeRange,
                    'added'   => $addedRange,
                );

                $fileDiff = substr( $fileDiff, strlen( $match[0] ) );
            }
        }

        file_put_contents( $this->resultDir . '/diff.php', "<?php\n\nreturn " . var_export( $parsedDiff, true ) . ";\n\n" );
    }

    /**
     * Process annotations from summary XML file
     *
     * @return void
     */
    protected function processAnnotations()
    {
        $diff = include $this->resultDir . '/diff.php';

        foreach ( $diff as $file => $fileDiff )
        {
            foreach ( $fileDiff as $diffPart )
            {
                switch ( $diffPart['type'] )
                {
                    case 'a':
                    case 'c':
                        foreach ( $diffPart['added'] as $line )
                        {
                            $this->gateway->create( new Struct\Annotation( $file, $line, null, 'diff', 'added' ) );
                        }
                        break;
                }
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
     * Check if menu should be show at all
     *
     * @return bool
     */
    public function displayable()
    {
        return is_file( $this->resultDir . '/diff.php' );
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
        if ( !is_file( $this->resultDir . '/diff.php' ) )
        {
            return new Struct\Response(
                'not_available.twig',
                array(
                    'summary'  => $this->getSummary(),
                )
            );
        }

        $fileChanges = array();
        $maxChanges  = 0;

        $diff = include $this->resultDir . '/diff.php';
        foreach ( $diff as $file => $fileDiff )
        {
            if ( preg_match( '(^\\.|/\\.)', $file ) )
            {
                // Ignore all dot files
                continue;
            }

            $fileChanges[$file] = array(
                'removed' => 0,
                'added'   => 0,
            );

            foreach ( $fileDiff as $diffPart )
            {
                $fileChanges[$file]['removed'] += count( $diffPart['removed'] );
                $fileChanges[$file]['added']   += count( $diffPart['added'] );
            }

            $maxChanges = max( $maxChanges, $fileChanges[$file]['removed'], $fileChanges[$file]['added'] );
        }

        return new Struct\Response(
            'diff.twig',
            array(
                'changes' => $fileChanges,
                'max'     => $maxChanges,
            )
        );
    }
}

