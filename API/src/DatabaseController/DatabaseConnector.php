<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseController;

use Exception;

/*
handles database connection.

  partly taken from: https://developer.okta.com/blog/2019/03/08/simple-rest-api-php

*/
class DatabaseConnector {

    static private $dbConnection = null;

    //get the PDO connection object
    static public function getConnection() : \PDO {

        //check if the connection is already made
        if(is_null(self::$dbConnection)){
            throw new Exception("No Connection");
        }

        return self::$dbConnection;
    } 

    //start the Database connection
    static public function connect() {

        //get all required dotEnv Variables
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db   = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        //start the connection and store to $dbConnection
        self::$dbConnection = new \PDO(
            "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
            $user,
            $pass);
    }
}
?>