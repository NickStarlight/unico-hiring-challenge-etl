<?php

declare(strict_types=1);

namespace UnicoETL\Helpers;

use Exception;
use PDO;
use PDOException;

/**
 * Class PG
 * Implements a simple interface for interacting with a PostgreSQL database.
 *
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
final class PG
{
    /**
     * Holds the PDO connection object.
     * 
     * @var PDO
     */
    protected PDO $connection;

    /** 
     * Initializes the PostgreSQL PDO connection.
     * 
     * @throws Exception|PDOException
     * @return PDO
     */
    public function __construct()
    {
        $pgHost = getenv('PG_HOST');
        $pgDb = getenv('PG_DB');
        $pgUser = getenv('PG_USER');
        $pgPassword = getenv('PG_PASSWORD');
        $dsn = "pgsql:host=$pgHost;dbname=$pgDb";

        /** Just to be safe, you never know */
        if ($pgHost === false || $pgDb === false || $pgUser === false || $pgPassword === false) {
            throw new Exception('Unable to find database configuration in the environment, check your .env file');
        }

        try {
            $this->connection = new PDO($dsn, $pgUser, $pgPassword);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            Logger::Error($e);
            throw new Exception('Unable to connect to the database, please check the log file.');
        }
    }

    /** 
     * Returns the context PDO instance.
     * 
     * @return PDO
     */
    public function getDB(): PDO
    {
        return $this->connection;
    }
}
