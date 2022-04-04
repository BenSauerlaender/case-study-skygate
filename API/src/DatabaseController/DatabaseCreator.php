<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseController;

//create DB-tables if there are not allready there.
class DatabaseCreator {

    //create all tables
    static public function create(){

        //get database connection
        $pdo = DatabaseConnector::getConnection();

        foreach (self::TABLES as $t){
            $pdo->exec($t);
        }
    }

    private const TABLES = [

        'CREATE TABLE IF NOT EXISTS role(
            role_id     INT             AUTO_INCREMENT,
            name        VARCHAR(100)    NOT NULL,

            role_read   BOOLEAN         NOT NULL, 
            role_write  BOOLEAN         NOT NULL,
            role_delete BOOLEAN         NOT NULL,

            user_read   BOOLEAN         NOT NULL,
            user_write  BOOLEAN         NOT NULL,
            user_delete BOOLEAN         NOT NULL,

            PRIMARY KEY (role_id),
            UNIQUE (name)
        );',


        'CREATE TABLE IF NOT EXISTS user(
            user_id     INT             AUTO_INCREMENT,

            email       VARCHAR(100)    NOT NULL,
            name        VARCHAR(100)    NOT NULL,
            postcode    VARCHAR(5)      NOT NULL,
            city        VARCHAR(50)     NOT NULL,
            phone       VARCHAR(20)     NOT NULL,

            salt        BINARY(60)      NOT NULL,
            hashed_pass BINARY(60)      NOT NULL,

            role_id     INT             NOT NULL,

            PRIMARY KEY (user_id),
            FOREIGN KEY (role_id) REFERENCES role(role_id),
            UNIQUE (email)
        );'
    ];



}
?>