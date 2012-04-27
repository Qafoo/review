<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\DIC;
use Qafoo\Review\DIC;
use Qafoo\Review;

/**
 * Base DIC
 *
 * @version $Revision$
 *
 * @property-read \Puppeteer\Commons\Configuration\Ini $configuration
 *                Main component configuration.
 * @property-read \Puppeteer\Commons\MySQLi $mysqli
 *                Used database handle.
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
        'configuration' => true,
        'mysqli'        => true,
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

        $this->configuration = function ( $dic )
        {
            return new Review\Configuration(
                $dic->srcDir . '/config/config.ini',
                $dic->environment
            );
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
    }
}

