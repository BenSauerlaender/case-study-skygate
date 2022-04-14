<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller;

use PDO;

/**
 * Handles database table creation
 */
class MySqlTableCreator
{

    /**
     * Create all tables
     *
     * @param  PDO $pdo
     */
    static public function create(PDO $pdo): void
    {
        //create all tables via SQL
        foreach (self::TABLES as $t) {
            $pdo->exec($t);
        }
    }

    //SQL - CREATE Table - Statements for each table
    private const TABLES = [

        'CREATE TABLE IF NOT EXISTS role(
            role_id     INT             NOT NULL,
            name        VARCHAR(100)    NOT NULL,

            role_read   BOOLEAN         NOT NULL, 
            role_write  BOOLEAN         NOT NULL,
            role_delete BOOLEAN         NOT NULL,

            user_read   BOOLEAN         NOT NULL,
            user_write  BOOLEAN         NOT NULL,
            user_delete BOOLEAN         NOT NULL,

            created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            updated_at DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (role_id),
            UNIQUE (name)
        );',


        'CREATE TABLE IF NOT EXISTS user(
            user_id             INT             AUTO_INCREMENT,

            email               VARCHAR(100)    NOT NULL,
            name                VARCHAR(100)    NOT NULL,
            postcode            VARCHAR(5)      NOT NULL,
            city                VARCHAR(50)     NOT NULL,
            phone               VARCHAR(20)     NOT NULL,

            hashed_pass         BINARY(60)      NOT NULL,

            verified            BOOLEAN         NOT NULL ,
            verification_code   VARCHAR(10), 

            role_id             INT             NOT NULL,

            created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (user_id),
            FOREIGN KEY (role_id) REFERENCES role(role_id),
            UNIQUE (email)
        );',


        'CREATE TABLE IF NOT EXISTS emailChangeRequest(
            request_id          INT             AUTO_INCREMENT,

            user_id             INT             NOT NULL,
            new_email           VARCHAR(100)    NOT NULL,
            verification_code   VARCHAR(10)     NOT NULL, 

            created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (request_id),
            FOREIGN KEY (user_id) REFERENCES user(user_id),
            UNIQUE (new_email),
            UNIQUE (user_id)
        );'
    ];
}
