<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Controller\MySqlTableCreator;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\BaseDatabaseTest;
use PDO;
use PDOException;

/**
 * Testsuit for the role table creation from MySqlTableCreator 
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

        $this->assertNotFalse($stmt);

        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertNotFalse($response);

        $this->assertContains("role", $response);
    }

    /**
     * Tests if the role table has all Columns
     */
    public function testRoleTableHasAllColumns(): void
    {
        $stmt = self::$pdo->query('
            DESCRIBE role;
        ');

        $this->assertNotFalse($stmt);

        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertNotFalse($response);

        $expectedColumns = ["role_id", "name", "role_read", "role_write", "role_delete", "user_read", "user_write", "user_delete", "created_at", "updated_at"];

        //compares both arrays but ignores the order
        $this->assertEqualsCanonicalizing($expectedColumns, $response);
    }

    /**
     * Tests if a table insert only works with all necessary values
     * 
     * @dataProvider incompleteInsertProvider
     */
    public function testRoleInsertFailsWithoutAllNecessaryValues(string $insert): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("doesn't have a default value");

        self::$pdo->exec($insert);
    }

    public function incompleteInsertProvider(): array
    {

        return [
            "missing role_id" => ['
                INSERT INTO role
                    (name, role_read, role_write, role_delete, user_read, user_write, user_delete)
                VALUES 
                    ("admin",true,true,true,true,true,true);
            '],
            "missing name" => ['
                INSERT INTO role
                    (role_id, role_read, role_write, role_delete, user_read, user_write, user_delete)
                VALUES 
                    (0,true,true,true,true,true,true);
            ']
        ];
    }

    public function testRoleInsertDefaultValues(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (role_id, name)
                VALUES 
                    (0,"name");
        ');

        $response = self::$pdo->query('
            SELECT role_read, role_write, role_delete, user_read, user_write, user_delete FROM role;
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([
            [
                "role_read" => 0,
                "role_write" => 0,
                "role_delete" => 0,
                "user_read" => 0,
                "user_write" => 0,
                "user_delete" => 0
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
                    (role_id, name)
                VALUES 
                    (0,"admin"),
                    (1,"admin");');
    }

    /**
     * tests if the insert fails, if trying to insert 2 roles with the same id
     */
    public function testRoleInsertFailsByDuplicateID(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO role
                    (role_id, name)
                VALUES 
                    (0,"admin"),
                    (0,"user");');
    }

    /**
     * Tests if the Role deletion fails if the role has currently an assigned user
     */
    public function testDeleteFailsIfRoleHasAnAssignedUser(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (role_id, name)
                VALUES 
                    (0,"admin");');

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin3@mail.de","admin","00000","admintown","015937839",1,true,0);');


        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("a foreign key constraint fails");

        self::$pdo->exec('DELETE FROM role WHERE role_id=0');
    }

    /**
     * Tests if the created_at and updated_at field is set correctly by an insert
     */
    public function testCreatedAndUpdatedAtIfInserted(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (role_id, name)
                VALUES 
                    (0,"admin"),
                    (1,"user");');

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
    public function testUpdated_atChangedOnUpdate(): void
    {
        self::$pdo->exec('
                INSERT INTO role
                    (role_id, name)
                VALUES 
                    (0,"user");');

        sleep(1);

        self::$pdo->exec(' UPDATE role SET name="newName" WHERE role_id=0;');

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
        self::$pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['DB_DATABASE'] . ";");
    }
}
