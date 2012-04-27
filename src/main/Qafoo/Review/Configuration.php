<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;

/**
 * Base class for an *.ini based configuration implementation.
 *
 * @version $Revision$
 */
class Configuration
{
    /**
     * @var array
     */
    private $inheritance = array(
        'development'  =>  'testing',
        'testing'      =>  'staging',
        'staging'      =>  'production'
    );

    /**
     * @var array
     */
    private $configuration;

    /**
     * Constructs a new ini based configuration instance.
     *
     * @param string $iniFile
     * @param string $environment
     */
    public function __construct( $iniFile, $environment )
    {
        $configuration = parse_ini_file( $iniFile, true );

        if ( false === isset( $configuration[$environment] ) )
        {
            throw new \UnexpectedValueException( "Unknown environment $environment." );
        }

        $this->configuration = $configuration[$environment];

        $parent = $environment;
        while ( isset( $this->inheritance[$parent] ) )
        {
            $parent = $this->inheritance[$parent];

            if ( isset( $configuration[$parent ] ) )
            {
                $this->configuration = array_merge(
                    $configuration[$parent],
                    $this->configuration
                );
            }
        }
    }

    /**
     * Returns the configuration value for the given $key.
     *
     * @param string $key
     * @return string
     */
    public function __get( $key )
    {
        if ( isset( $this->configuration[$key] ) )
        {
            return $this->configuration[$key];
        }

        throw new \InvalidArgumentException( $key );
    }
}

