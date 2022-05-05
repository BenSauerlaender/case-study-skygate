<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Database;

use BenSauer\CaseStudySkygateApi\Utilities\MySqlConnector;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base class for all Database tests
 * 
 * Handles the database connection
 */
abstract class BaseDatabaseTest extends TestCase
{
    /**
     * The database connection object
     *
     * @var PDO|null
     */
    protected static ?PDO $pdo;

    /**
     * Connects to the database
     * 
     * also create all tables
     */
    public static function setUpBeforeClass(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../test.env");
        $dotenv->load();

        self::$pdo = MySqlConnector::getConnection();

        self::$pdo->exec("DROP DATABASE IF EXISTS " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");
    }

    /**
     * Disconnects from the database
     */
    public static function tearDownAfterClass(): void
    {
        //close connection
        self::$pdo = null;
        MySqlConnector::closeConnection();
    }
}
