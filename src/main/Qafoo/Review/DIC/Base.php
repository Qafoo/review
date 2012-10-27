<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\DIC;
use Qafoo\Review\DIC;
use Qafoo\Review;

/**
 * Base DIC
 *
 * @version $Revision$
 * @license APGLv3
 *
 * @property-read \Qafoo\Review\Configuration $configuration
 *                Main component configuration.
 * @property-read \Qafoo\Review\MySQLi $mysqli
 *                Used database handle.
 * @property-read \Twig_Environment $twig
 *                Twig environment (template engine)
 * @property-read \Qafoo\Review\View\Twig $view
 *                Twig base view
 */
class Base extends DIC
{
    /**
     * Array with names of objects, which are always shared inside of this DIC
     * instance.
     *
     * @var array(string)
     */
    protected $alwaysShared = array(
        'srcDir'            => true,
        'resultDir'         => true,
        'configuration'     => true,
        'mysqli'            => true,
        'view'              => true,
        'twig'              => true,
        'annotationGateway' => true,
        'sourceController'  => true,
        'analyzers'         => true,
        'reviewController'  => true,
    );

    /**
     * Initialize DIC values
     *
     * @return void
     */
    public function initialize()
    {
        $this->srcDir = function ( $dic )
        {
            return substr( __DIR__, 0, strpos( __DIR__, '/src/' ) + 4 );
        };

        $this->environment = function ( $dic )
        {
            if ( !is_file( $file = $dic->srcDir . '/../build.properties.local' ) )
            {
                return 'production';
            }

            $config = @parse_ini_file( $file );
            if ( !isset( $config['commons.env'] ) )
            {
                return 'production';
            }

            return $config['commons.env'];
        };

        $this->debug = function ( $dic )
        {
            return (
                $dic->environment === 'development' ||
                $dic->environment === 'testing'
            );
        };

        $this->resultDir = function ( $dic )
        {
            return $dic->srcDir . '/results';
        };

        $this->source = function ( $dic )
        {
            return $dic->resultDir . '/source';
        };

        $this->configuration = function ( $dic )
        {
            return new Review\Configuration(
                $dic->srcDir . '/config/config.ini',
                $dic->environment
            );
        };

        $this->twig = function ( $dic )
        {
            $twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem( $dic->srcDir . '/templates' ),
                array(
//                    'cache' => $dic->srcDir . '/cache'
                )
            );

            $twig->addExtension( new Review\View\Twig\Extension() );

            return $twig;
        };

        $this->view = function( $dic )
        {
            return new Review\View\Twig( $dic->twig );
        };

        $this->mysqli = function ( $dic )
        {
            return new Review\MySQLi(
                $dic->configuration->hostname,
                $dic->configuration->username,
                $dic->configuration->password,
                $dic->configuration->database
            );
        };

        $this->annotationGateway = function ( $dic )
        {
            return new Review\AnnotationGateway\Mysqli(
                $dic->mysqli
            );
        };

        $this->codeProcessorFactory = function ( $dic )
        {
            return new Review\CodeProcessorFactory();
        };

        $this->sourceController = function ( $dic )
        {
            return new Review\Controller\Source(
                $dic->resultDir,
                $dic->source,
                $dic->annotationGateway,
                $dic->codeProcessorFactory
            );
        };

        $this->pdependModel = function ( $dic )
        {
            return new Review\Analyzer\PDepend\Model();
        };

        $this->calculatorModel = function ( $dic )
        {
            return new Review\Analyzer\Calculator\Model();
        };

        $this->analyzers = function ( $dic )
        {
            return array(
                'pdepend' => new Review\Analyzer\PDepend( $dic->resultDir, $dic->annotationGateway, $this->pdependModel, $this->codeProcessorFactory ),
                'calc'    => new Review\Analyzer\Calculator( $dic->resultDir, $dic->annotationGateway, $this->calculatorModel ),
                'phpmd'   => new Review\Analyzer\Phpmd( $dic->resultDir, $dic->annotationGateway ),
                'diff'    => new Review\Analyzer\Diff( $dic->resultDir, $dic->annotationGateway ),
                'uml'     => new Review\Analyzer\UML( $dic->resultDir, $dic->annotationGateway ),
                // 'phplint' => new Review\Analyzer\Phplint( $dic->resultDir, $dic->annotationGateway ),
                'phpcpd'  => new Review\Analyzer\Phpcpd( $dic->resultDir, $dic->annotationGateway ),
                // 'oxid'    => new Review\Analyzer\OxPhpmd( $dic->resultDir, $dic->annotationGateway ),
            );
        };

        $this->reviewController = function ( $dic )
        {
            return new Review\Controller\Review(
                $dic->sourceController,
                $this->analyzers,
                $dic->annotationGateway
            );
        };
    }
}

