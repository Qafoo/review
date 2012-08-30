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
        if ( !is_file( $iniFile ) &&
             is_file( $iniFile . '.dist' ) )
        {
            $iniFile = $iniFile . '.dist';
        }

        $this->parseIniFile( $iniFile, $environment );
    }

    /**
     * Parse ini file
     *
     * @param string $iniFile
     * @param string $environment
     * @return void
     */
    public function parseIniFile( $iniFile, $environment )
    {
        $configuration = parse_ini_file( $iniFile, true );

        if ( false === isset( $configuration[$environment] ) )
        {
            throw new \UnexpectedValueException( "Unknown environment $environment." );
        }

        $this->configuration = $configuration[$environment];
        $this->applyInheritance( $configuration, $environment );
        foreach ( $this->configuration as $key => $value )
        {
            if ( strpos( $key, '.' ) === false )
            {
                continue;
            }

            $path = array_filter( explode( '.', $key ) );
            unset( $this->configuration[$key] );
            $current = &$this->configuration;
            foreach ( $path as $element )
            {
                if ( !isset( $current[$element] ) )
                {
                    $current[$element] = array();
                }

                $current = &$current[$element];
            }
            $current = $value;
        }
    }

    /**
     * Inherit configuration options from upper level environments
     *
     * @param array $configuration
     * @param string $environment
     * @return void
     */
    protected function applyInheritance( array $configuration, $environment )
    {
        $parent = $environment;
        while ( isset( $this->inheritance[$parent] ) )
        {
            $parent = $this->inheritance[$parent];

            if ( isset( $configuration[$parent] ) )
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

        throw new \OutOfBoundsException( "No configuration option $key available." );
    }
}

