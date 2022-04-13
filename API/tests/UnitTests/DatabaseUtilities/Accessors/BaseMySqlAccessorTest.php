<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors;

//load composer dependencies
require 'vendor/autoload.php';

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlConnector;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base class for all MySqlAccessor tests
 * 
 * Handles the database connection
 */
abstract class BaseMySqlAccessorTest extends TestCase
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
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "test.env");
        $dotenv->load();

        self::$pdo = MySqlConnector::getConnection();
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

    /**
     * Deletes and re-creates the database and tables
     */
    protected static function resetDB(): void
    {
        self::$pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE'] . ";");

        //create tables
        MySqlTableCreator::create(self::$pdo);
    }

    /**
     * Counts changed rows and performs an "assertEquals" on the expected and actual result
     * 
     * @param int $expected The expected number of changed rows
     */
    protected function assertChangedRowsEquals(int $expected): void
    {

        self::$pdo->exec('
            //TODO
        ');

        $changedRows;

        $this->assertEquals($expected, $changedRows);
    }
}
