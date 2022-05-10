<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;
use PDO;
use PDOException;

/**
 * Handles database connection.
 * 
 * partly taken from: https://developer.okta.com/blog/2019/03/08/simple-rest-api-php
 * 
 * @codeCoverageIgnore
 */
class MySqlConnector
{
    static private ?PDO $db = null;

    //get the PDO connection object
    /**
     * Get the database connection object
     * 
     * @return PDO The database connection object.
     * 
     * @throws DBException  if the attempt to connect to the Requested database fails.
     */
    static public function getConnection(): PDO
    {

        //check if the connection is already made
        if (is_null(self::$db)) {
            self::startConnection();
        }

        //return the PDO connection object
        return self::$db;
    }

    /**
     * start the Database connection
     *
     * @throws DBException if the attempt to connect to the Requested database fails.
     */
    private static function startConnection()
    {
        //get all required dotEnv Variables
        $host = $_ENV['MYSQL_HOST'];
        $port = $_ENV['MYSQL_PORT'];
        $user = $_ENV['MYSQL_USER'];
        $pass = $_ENV['MYSQL_PASSWORD'];

        if (strlen($user) === 0) $user = "root";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            //start the connection and store to $dbConnection
            self::$db = new PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4;",
                $user,
                $pass,
                $options
            );
            //select Database
            self::$db->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");

            date_default_timezone_set($_ENV['TIMEZONE']);
            $offset = date('P');
            self::$db->exec("SET time_zone='$offset';");
        } catch (PDOException $e) { //prevents username and password from being in the stacktrace.
            throw new DBException("", 0, new PDOException($e->getMessage(), (int)$e->getCode()));
        }
    }

    /**
     * Closes the database connection
     */
    public static function closeConnection()
    {
        self::$db = null;
    }
}
