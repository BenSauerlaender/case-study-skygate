<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Controller\MySqlTableCreator;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\BaseDatabaseTest;
use PDO;

/**
 * Testsuit for the user table creation from MySqlTableCreator 
 */
final class UserTableTest extends BaseDatabaseTest
{
    /**
     * Tests if the user table was created
     */
    public function testUserTableCreated(): void
    {
        $stmt = self::$pdo->query('
            SHOW TABLES;
        ');

        $this->assertNotFalse($stmt);

        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertNotFalse($response);

        $this->assertContains("user", $response);
    }

    /**
     * Tests if the user table has all Columns
     */
    public function testUserTableHasAllColumns(): void
    {
        $stmt = self::$pdo->query('
            DESCRIBE user;
        ');

        $this->assertNotFalse($stmt);

        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertNotFalse($response);

        $expectedColumns = ["user_id", "email", "name", "postcode", "city", "phone", "hashed_pass", "verified", "verification_code", "role_id", "created_at", "updated_at"];

        $this->assertEqualsCanonicalizing($expectedColumns, $response);
    }

    /**
     * Tests if a table insert only works with all necessary values
     */
    public function testUserInsertFailsWithoutAllNecessaryValues(): void
    {
    }

    public function setUp(): void
    {
        //create tables
        MySqlTableCreator::create(self::$pdo);
    }

    public function tearDown(): void
    {
        //nuke the db
        self::$pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['DB_DATABASE'] . ";");
    }
}
