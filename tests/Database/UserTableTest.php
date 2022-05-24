<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Database;

use BenSauer\CaseStudySkygateApi\tests\helper\TableCreator;
use PDO;
use PDOException;

/**
 * Test suite for the user table creation 
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

        $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains("user", $allTables);
    }

    /**
     * Tests if the user table has all Columns
     */
    public function testUserTableHasAllColumns(): void
    {
        $stmt = self::$pdo->query('
            DESCRIBE user;
        ');

        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $expectedColumns = ["user_id", "email", "name", "postcode", "city", "phone", "hashed_pass", "verified", "verification_code", "role_id", "created_at", "updated_at"];

        //compares both arrays but ignores the order
        $this->assertEqualsCanonicalizing($expectedColumns, $columns);
    }

    /**
     * Tests if a table insert only works with all necessary values
     * 
     * @dataProvider incompleteInsertProvider
     */
    public function testUserInsertFailsWithoutAllNecessaryValues(string $insert): void
    {
        $this->insertRole();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("doesn't have a default value");

        self::$pdo->exec($insert);
    }

    public function incompleteInsertProvider(): array
    {
        return [
            "missing email" => [' 
                INSERT INTO user
                    (name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin","00000","admintown","015937839","1",true,1);
            '],
            "missing name" => [' 
                INSERT INTO user
                    (email, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin@mail.de","00000","admintown","015937839","1",true,1);
            '],
            "missing postcode" => [' 
                INSERT INTO user
                    (email, name, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin@mail.de","admin","admintown","015937839","1",true,1);
            '],
            "missing city" => [' 
                INSERT INTO user
                    (email, name, postcode, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin@mail.de","admin","00000","015937839","1",true,1);
            '],
            "missing phone" => [' 
                INSERT INTO user
                    (email, name, postcode, city, hashed_pass, verified, role_id)
                VALUES 
                    ("admin@mail.de","admin","00000","admintown","1",true,1);
            '],
            "missing hashedPass" => [' 
                INSERT INTO user
                    (email, name, postcode, city, phone, verified, role_id)
                VALUES 
                    ("admin@mail.de","admin","00000","admintown","015937839",true,1);
            '],
            "missing verified" => [' 
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, role_id)
                VALUES 
                    ("admin@mail.de","admin","00000","admintown","015937839","1",1);
            '],
            "missing role_id" => [' 
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified)
                VALUES 
                    ("admin@mail.de","admin","00000","admintown","015937839","1",true);
            '],
        ];
    }

    /**
     * Tests if the insert fails if the specified role_id dont have a corresponding role 
     */
    public function testUserInsertFailsIfRoleNotExists(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("a foreign key constraint fails");

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin@mail.de","admin","00000","admintown","015937839","1",true,2); ');
    }

    /**
     * Tests if the userId increments automatically
     */
    public function testUserInsertIDAutoIncrement(): void
    {
        $this->insertRole();

        $ret = self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin1@mail.de","admin","00000","admintown","015937839","1",true,1),
                    ("admin2@mail.de","admin","00000","admintown","015937839","1",true,1),
                    ("admin3@mail.de","admin","00000","admintown","015937839","1",true,1) ; ');

        $this->assertEquals(3, $ret);

        $response = self::$pdo->query('
            SELECT user_id, email FROM user;
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([
            ["user_id" => 1, "email" => "admin1@mail.de"],
            ["user_id" => 2, "email" => "admin2@mail.de"],
            ["user_id" => 3, "email" => "admin3@mail.de"]
        ], $response);
    }

    /**
     * tests if the insert fails, if trying to insert 2 users with the same email address
     */
    public function testUserInsertFailsByDuplicateEmail(): void
    {
        $this->insertRole();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin1@mail.de","admin","00000","admintown","015937839","1",true,1),
                    ("admin3@mail.de","admin","00000","admintown","015937839","1",true,1),
                    ("admin3@mail.de","admin","00000","admintown","015937839","1",true,1);');
    }

    /**
     * tests if the insert fails, if trying to insert 2 users with the same id
     */
    public function testUserInsertFailsByDuplicateID(): void
    {
        $this->insertRole();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO user
                    (user_id, email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    (1,"admin1@mail.de","admin","00000","admintown","015937839","1",true,1),
                    (1,"admin2@mail.de","admin","00000","admintown","015937839","1",true,1),
                    (2,"admin3@mail.de","admin","00000","admintown","015937839","1",true,1);');
    }

    /**
     * Tests if the User deletion fails if the user has currently an email change request
     */
    public function testDeleteFailsIfUserHasAnEmailChangeRequest(): void
    {
        $this->insertRole();

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin3@mail.de","admin","00000","admintown","015937839","1",true,1);');

        self::$pdo->exec('
                INSERT INTO emailChangeRequest
                    (user_id, new_email, verification_code)
                VALUES 
                    (1,"admin4@mail.de","code");');


        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("a foreign key constraint fails");

        self::$pdo->exec('DELETE FROM user WHERE user_id=1');
    }

    /**
     * Tests if the User deletion fails if the user has an refresh token count
     */
    public function testDeleteFailsIfUserHasAnRefreshTokenCount(): void
    {
        $this->insertRole();

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin3@mail.de","admin","00000","admintown","015937839","1",true,1);');

        self::$pdo->exec('
                INSERT INTO refreshToken
                    (user_id, count)
                VALUES 
                    (1,0);');


        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("a foreign key constraint fails");

        self::$pdo->exec('DELETE FROM user WHERE user_id=1');
    }

    /**
     * Tests if the created_at and updated_at field is set correctly by an insert
     */
    public function testCreatedAndUpdatedAtIfInserted(): void
    {
        $this->insertRole();

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin@mail.de","admin","00000","admintown","015937839","1",true,1),
                    ("admin1@mail.de","admin","00000","admintown","015937839","1",true,1);');

        $time = time();

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM user;
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
        $this->insertRole();

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin@mail.de","admin","00000","admintown","015937839","1",true,1);');

        sleep(1);

        self::$pdo->exec(' UPDATE user SET name="newName" WHERE user_id=1;');

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM user;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //updated_at and created at are not equal
        $this->assertTrue($response[0]["created_at"] !== $response[0]["updated_at"]);
    }

    /**
     * Inserts a role into the role table
     */
    private function insertRole(): void
    {
        self::$pdo->exec('
            INSERT INTO role
                (name)
            VALUES 
                ("admin");');
    }

    public function setUp(): void
    {
        //create tables
        TableCreator::create(self::$pdo);
    }

    public function tearDown(): void
    {
        //nuke the db
        self::$pdo->exec("DROP DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");
    }
}
