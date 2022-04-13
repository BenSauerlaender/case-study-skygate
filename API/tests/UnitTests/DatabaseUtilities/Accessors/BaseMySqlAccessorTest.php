<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors;

//load composer dependencies
require 'vendor/autoload.php';

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlConnector;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base class for all MySqlAccessor tests
 * 
 * Handles the database connection
 */
final class BaseMySqlAccessorTest extends TestCase
{
    /**
     * The database connection object
     *
     * @var PDO|null
     */
    protected static ?PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "test.env");
        $dotenv->load();

        self::$pdo = MySqlConnector::getConnection();
    }

    public static function tearDownAfterClass(): void
    {
        //nuke all data
        self::$pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE']);
        self::$pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE']);

        //close connection
        self::$pdo = null;
        MySqlConnector::closeConnection();
    }
}
