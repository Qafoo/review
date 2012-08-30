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
 * Base MySQLi connection class
 *
 * @version $Revision$
 * @license APGLv3
 */
class MySQLi extends \MySQLi
{
    /**
     * Construct from optional connection data
     *
     * If connection information is not provided, it will be read from the
     * corresponding ini options.
     *
     * @param string $host
     * @param string $username
     * @param string $passwd
     * @param string $dbname
     * @param string $port
     * @param string $socket
     * @return void
     */
    public function __construct( $host = null, $username = null, $passwd = null, $dbname = null, $port = null, $socket = null )
    {
        $host     = $host ?: ini_get( 'mysqli.default_host' );
        $username = $username ?: ini_get( 'mysqli.default_user' );
        $passwd   = $passwd ?: ini_get( 'mysqli.default_pw' );
        $dbname   = $dbname ?: '';
        $port     = $port ?: ini_get( 'mysqli.default_port' );
        $socket   = $socket ?: ini_get( 'mysqli.default_socket' );

        @parent::__construct( $host, $username, $passwd, $dbname, $port, $socket );

        if ( $this->connect_errno )
        {
            throw new \RuntimeException( "Could not connect to database: " . $this->connect_error );
        }

        \mysqli_report( \MYSQLI_REPORT_STRICT /* | \MYSQLI_REPORT_INDEX */ );
        $this->set_charset( "utf8" );
    }

    /**
     * Performs a query on the database
     *
     * @param string $query
     * @param int $resultmode
     * @throws MySQLi\QueryException
     * @return bool
     */
    public function query( $query, $resultmode = \MYSQLI_STORE_RESULT )
    {
        if ( ( $result = parent::query( $query, $resultmode ) ) === false )
        {
            throw new \mysqli_sql_exception( $this->error );
        }

        return $result;
    }
}

