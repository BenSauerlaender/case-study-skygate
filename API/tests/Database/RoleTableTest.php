<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Database;

use BenSauer\CaseStudySkygateApi\Utilities\MySqlTableCreator;
use PDO;
use PDOException;

/**
 * Testsuit for the role table creation 
 */
final class RoleTableTest extends BaseDatabaseTest
{
    /**
     * Tests if the role table was created
     */
    public function testRoleTableCreated(): void
    {
        $stmt = self::$pdo->query('
            SHOW TABLES;
        ');

        $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains("role", $allTables);
    }

    /**
     * Tests if the role table has all Columns
     */
    public function testRoleTableHasAllColumns(): void
    {
        $stmt = self::$pdo->query('
            DESCRIBE role;
        ');

        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $expectedColumns = ["role_id", "name", "permissions", "created_at", "updated_at"];

        //compares both arrays but ignores the order
        $this->assertEqualsCanonicalizing($expectedColumns, $columns);
    }

    /**
     * Tests if a table insert throws an exception if no name was given
     */
    public function testRoleInsertFailsWithoutName(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("doesn't have a default value");

        self::$pdo->exec('
                INSERT INTO role
                    (role_id, permissions)
                VALUES 
                    (0,"");
            ');
    }

    /**
     * Tests if the role_id increments automatically
     */
    public function testRoleInsertIDAutoIncrement(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (name)
                VALUES 
                    ("name1"),
                    ("name2"),
                    ("name3");
        ');

        $response = self::$pdo->query('
            SELECT role_id, name FROM role;
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([
            ["role_id" => 1, "name" => "name1"],
            ["role_id" => 2, "name" => "name2"],
            ["role_id" => 3, "name" => "name3"]
        ], $response);
    }

    public function testRoleInsertDefaultValues(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (name)
                VALUES 
                    ("name");
        ');

        $response = self::$pdo->query('
            SELECT permissions FROM role;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //check all not set values
        $this->assertEquals([
            [
                "permissions" => ""
            ]
        ], $response);
    }

    /**
     * tests if the insert fails, if trying to insert 2 roles with the same name
     */
    public function testRoleInsertFailsByDuplicateName(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO role
                    (name)
                VALUES 
                    ("admin"),
                    ("admin");');
    }

    /**
     * Tests if the Role deletion fails if the role has currently an assigned user
     */
    public function testDeleteFailsIfRoleHasAnAssignedUser(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (name)
                VALUES 
                    ("admin");');

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin3@mail.de","admin","00000","admintown","015937839",1,true,1);');


        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("a foreign key constraint fails");

        self::$pdo->exec('DELETE FROM role WHERE role_id=1');
    }

    /**
     * Tests if the created_at and updated_at field is set correctly by an insert
     */
    public function testCreatedAndUpdatedAtIfInserted(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (name)
                VALUES 
                    ("admin"),
                    ("user");');

        $time = time();

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM role;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //all dates equal
        $this->assertTrue($response[0]["created_at"] === $response[0]["updated_at"] and $response[1]["created_at"] === $response[1]["updated_at"]);
        $this->assertTrue($response[0]["created_at"] === $response[1]["created_at"]);

        //difference less or equal then 1 second
        $this->assertTrue(abs(strtotime($response[0]["created_at"]) - $time) <= 1);
    }

    /**
     * Tests if the updated_at is updated correctly
     */
    public function testUpdatedAtChangedOnUpdate(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (name)
                VALUES 
                    ("user");');

        sleep(1);

        self::$pdo->exec(' UPDATE role SET name="newName" WHERE role_id=1;');

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM role;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //updated_at and created at are not equal
        $this->assertTrue($response[0]["created_at"] !== $response[0]["updated_at"]);
    }

    public function setUp(): void
    {
        //create tables
        MySqlTableCreator::create(self::$pdo);
    }

    public function tearDown(): void
    {
        //nuke the db
        self::$pdo->exec("DROP DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");
    }
}
