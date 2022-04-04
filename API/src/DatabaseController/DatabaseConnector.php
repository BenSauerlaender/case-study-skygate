<?php
//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseController;

use Exception;

//handles database connection
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

        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $db   = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME');
        $pass = getenv('DB_PASSWORD');

        self::$dbConnection = new \PDO(
            "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
            $user,
            $pass);
    }
}
?>