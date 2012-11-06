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
 * Main review controller
 *
 * @version $Revision$
 * @license APGLv3
 */
class Review
{
    /**
     * Analyzers utilized
     *
     * @var Analyzer[]
     */
    protected $analyzers;

    /**
     * Source controller
     *
     * @var Source
     */
    protected $sourceController;

    /**
     * Annotation gateway
     *
     * @var AnnotationGateway
     */
    protected $gateway;

    /**
     * Construct from analyzers
     *
     * @param Source $sourceController
     * @param Analyzer[] $analyzers
     * @param AnnotationGateway $gateway
     * @return void
     */
    public function __construct( Source $sourceController, array $analyzers = array(), AnnotationGateway $gateway )
    {
        $this->sourceController = $sourceController;
        $this->gateway          = $gateway;
        foreach ( $analyzers as $id => $analyzer )
        {
            $this->addAnalyzer( $id, $analyzer );
        }
    }

    /**
     * Add analyzer
     *
     * @param string $id
     * @param Analyzer $analyzer
     * @return void
     */
    public function addAnalyzer( $id, Analyzer $analyzer )
    {
        $this->analyzers[$id] = $analyzer;
    }

    /**
     * Analyze src dir
     *
     * @param string $path
     * @param string $oldPath
     * @return void
     */
    public function analyze( $path, $oldPath = null )
    {
        foreach ( $this->analyzers as $name => $analyzer )
        {
            echo " - Analyze with $name …";
            try
            {
                $analyzer->analyze( $path, $oldPath );
            }
            catch ( \SystemProcess\NonZeroExitCodeException $e )
            {
                $logFile = date( 'Ymd-His-' ) . $name . '.log';
                file_put_contents(
                    $logFile,
                    "Command failed with exit code {$e->exitCode}:\n" .
                    "{$e->command}\n" .
                    "\nSTDOUT:\n{$e->stdoutOutput}\n" .
                    "\nSTDERR:\n{$e->stderrOutput}\n"
                );

                echo " Fail, see $logFile for details.", PHP_EOL;

                continue;
            }
            catch ( \Exception $e )
            {
                echo " Fail: ", $e->getMessage(), PHP_EOL;
                continue;
            }
            echo " Done", PHP_EOL;
        }
    }

    /**
     * Get menu entries
     *
     * @return array
     */
    protected function getMenuEntries()
    {
        $analyzers = array();
        foreach ( $this->analyzers as $id => $analyzer )
        {
            if ( ( $analyzer instanceof Displayable ) &&
                 ( $analyzer->displayable() ) )
            {
                $analyzers[] = $entry = $analyzer->getMenuEntry();
                $entry->module = $id;
            }
        }

        return $analyzers;
    }

    /**
     * Show project overview
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function showOverview( RMF\Request $request )
    {
        $summaries = array();
        foreach ( $this->analyzers as $id => $analyzer )
        {
            $summaries[] = $summary = $analyzer->getSummary();
            $summary->module = $id;
        }

        return new Struct\Response(
            'overview.twig',
            array(
                'navigation'  => $this->getMenuEntries(),
                'summaries'   => $summaries,
                'annotations' => $this->gateway->getAnnotationsStats( array( 'user' ) ),
            )
        );
    }

    /**
     * Show analyzer results
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function showAnalyzer( RMF\Request $request )
    {
        if ( !isset( $request->variables['analyzer'] ) ||
             !isset( $this->analyzers[$request->variables['analyzer']] ) )
        {
            throw new \Exception( "Unknown / unspecified analyzer." );
        }

        $response = $this->analyzers[$request->variables['analyzer']]->render( $request );
        $response->data['navigation'] = $this->getMenuEntries();

        return $response;
    }

    /**
     * Show analyzer results
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function showSource( RMF\Request $request )
    {
        $response = $this->sourceController->show( $request );
        $response->data['navigation'] = $this->getMenuEntries();

        return $response;
    }
}

