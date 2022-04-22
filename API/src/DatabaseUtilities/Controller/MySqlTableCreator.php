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
 * 
 * @codeCoverageIgnore
 * 
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
            role_id     INT             AUTO_INCREMENT,
            name        VARCHAR(100)    NOT NULL,

            role_read   BOOLEAN         NOT NULL DEFAULT FALSE, 
            role_write  BOOLEAN         NOT NULL DEFAULT FALSE,
            role_delete BOOLEAN         NOT NULL DEFAULT FALSE,

            user_read   BOOLEAN         NOT NULL DEFAULT FALSE,
            user_write  BOOLEAN         NOT NULL DEFAULT FALSE,
            user_delete BOOLEAN         NOT NULL DEFAULT FALSE,

            created_at DATETIME(3)      NOT NULL DEFAULT NOW(3) ,
            updated_at DATETIME(3)      NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

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

            hashed_pass         VARCHAR(60)      NOT NULL,

            verified            BOOLEAN         NOT NULL ,
            verification_code   VARCHAR(10), 

            role_id             INT             NOT NULL,

            created_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ,
            updated_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

            PRIMARY KEY (user_id),
            FOREIGN KEY (role_id) REFERENCES role(role_id),
            UNIQUE (email)
        );',


        'CREATE TABLE IF NOT EXISTS emailChangeRequest(
            request_id          INT             AUTO_INCREMENT,

            user_id             INT             NOT NULL,
            new_email           VARCHAR(100)    NOT NULL,
            verification_code   VARCHAR(10)     NOT NULL, 

            created_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ,
            updated_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

            PRIMARY KEY (request_id),
            FOREIGN KEY (user_id) REFERENCES user(user_id),
            UNIQUE (new_email),
            UNIQUE (user_id)
        );'
    ];
}
