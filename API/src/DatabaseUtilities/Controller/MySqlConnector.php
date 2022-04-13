<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller;

use Exception;

/*
handles database connection.

  partly taken from: https://developer.okta.com/blog/2019/03/08/simple-rest-api-php

*/

class MySqlConnector
{
    static private ?\PDO $db = null;

    //get the PDO connection object
    /**
     * Get the database connection object
     * 
     * @return PDO The database connection object.
     * @throws if the attempt to connect to the requested database fails.
     */
    static public function getConnection(): \PDO
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
     * @throws if the attempt to connect to the requested database fails.
     */
    private static function startConnection()
    {
        //get all required dotEnv Variables
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db   = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        //start the connection and store to $dbConnection
        self::$db = new \PDO(
            "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
            $user,
            $pass
        );
    }

    /**
     * Closes the database connection
     */
    public static function closeConnection()
    {
        self::$db = null;
    }
}
