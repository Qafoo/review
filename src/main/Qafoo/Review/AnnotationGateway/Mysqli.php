<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\AnnotationGateway;
use Qafoo\Review\AnnotationGateway;
use Qafoo\Review\Struct;

/**
 * Base class for annotation gateways
 *
 * @version $Revision$
 */
class Mysqli extends AnnotationGateway
{
    /**
     * MySQLi connection
     *
     * @var \MySQLi
     */
    protected $connection;

    /**
     * Construct from database connection
     *
     * @param \MySQLi $connection
     * @return void
     */
    public function __construct( \MySQLi $connection )
    {
        $this->connection = $connection;
    }

    /**
     * Remove all existing annotations
     *
     * @return void
     */
    public function truncate()
    {
        $this->connection->query( sprintf( '
            TRUNCATE `annotation`
        ' ) );
    }

    /**
     * Get annotations for file
     *
     * @param string $file
     * @return void
     */
    public function getAnnotationsForFile( $file )
    {
        $result = $this->connection->query( sprintf( '
            SELECT
               `a_id`,
               `a_file`,
               `a_line`,
               `a_character`,
               `a_type`,
               `a_class`,
               `a_message`
            FROM
                `annotation`
            WHERE
                `a_file` = "%s"',
            $this->connection->escape_string( $file )
        ) );

        $annotations = array();
        while ( $row = $result->fetch_assoc() )
        {
            $annotations[] = $annotation = new Struct\Annotation();
            $annotation->id        = $row['a_id'];
            $annotation->file      = $row['a_file'];
            $annotation->line      = $row['a_line'];
            $annotation->character = $row['a_character'];
            $annotation->type      = $row['a_type'];
            $annotation->class     = $row['a_class'];
            $annotation->message   = $row['a_message'];
        }

        return $annotations;
    }

    /**
     * Create a new annotation
     *
     * @param Struct\Annotation $annotation
     * @return void
     */
    public function create( Struct\Annotation $annotation )
    {
        $this->connection->query( sprintf( '
            INSERT INTO
               `annotation`
            VALUES (
                null,
                "%s",
                %s,
                %s,
                "%s",
                "%s",
                "%s",
                null
            )',
            $this->connection->escape_string( $annotation->file ),
            (int) $annotation->line,
            (int) $annotation->character,
            $this->connection->escape_string( $annotation->type ),
            $this->connection->escape_string( $annotation->class ),
            $this->connection->escape_string( $annotation->message )
        ) );
    }
}

