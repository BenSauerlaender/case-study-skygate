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
 * Testsuit for the refreshToken table 
 */
final class RefreshTokenTableTest extends BaseDatabaseTest
{
    /**
     * Tests if the refreshToken table was created
     */
    public function testRefreshTokenTableCreated(): void
    {
        $stmt = self::$pdo->query('
            SHOW TABLES;
        ');

        $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains("refreshToken", $allTables);
    }

    /**
     * Tests if the refreshToken table has all Columns
     */
    public function testRefreshTokenTableHasAllColumns(): void
    {
        $stmt = self::$pdo->query('
            DESCRIBE refreshToken;
        ');

        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $expectedColumns = ["user_id", "count", "created_at", "updated_at"];

        //compares both arrays but ignores the order
        $this->assertEqualsCanonicalizing($expectedColumns, $columns);
    }

    public function testRefreshTokenInsertDefaultValues(): void
    {
        self::$pdo->exec('
                INSERT INTO refreshToken
                    (user_id)
                VALUES 
                    (1);
        ');

        $response = self::$pdo->query('
            SELECT user_id, count FROM refreshToken;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //check all not set values
        $this->assertEquals([
            [
                "user_id" => 1,
                "count" => 0
            ]
        ], $response);
    }

    /**
     * tests if the insert fails, if trying to insert 2 entries for the same user
     */
    public function testRefreshTokenInsertFailsByDuplicateUserID(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO refreshToken
                    (user_id)
                VALUES 
                    ("1"),
                    ("1");');
    }

    /**
     * Tests if a table insert throws an exception if no userID was given
     */
    public function testRefreshTokenInsertFailsWithoutUserID(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("doesn't have a default value");

        self::$pdo->exec('
                INSERT INTO refreshToken
                    (count)
                VALUES 
                    (0);
            ');
    }

    /**
     * Tests if the insert fails if the specified user_id dont have a corresponding user 
     */
    public function testRefreshTokenInsertFailsIfUserNotExists(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("a foreign key constraint fails");

        self::$pdo->exec('
                INSERT INTO refreshToken
                    (user_id)
                VALUES 
                    (3);');
    }



    /**
     * Tests if the created_at and updated_at field is set correctly by an insert
     */
    public function testCreatedAndUpdatedAtIfInserted(): void
    {
        self::$pdo->exec('
                INSERT INTO refreshToken
                    (user_id)
                VALUES 
                    ("1"),
                    ("2");');

        $time = time();

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM refreshToken;
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
                INSERT INTO refreshToken
                    (user_id)
                VALUES 
                    ("1");');

        sleep(1);

        self::$pdo->exec(' UPDATE refreshToken SET count="2" WHERE user_id=1;');

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM refreshToken;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //updated_at and created at are not equal
        $this->assertTrue($response[0]["created_at"] !== $response[0]["updated_at"]);
    }

    public function setUp(): void
    {
        //create tables
        MySqlTableCreator::create(self::$pdo);

        self::$pdo->exec('
            INSERT INTO role
                (name, role_read, role_write, role_delete, user_read, user_write, user_delete)
            VALUES 
                ("admin",true,true,true,true,true,true);');

        self::$pdo->exec('
                INSERT INTO user
                    (email, name, postcode, city, phone, hashed_pass, verified, role_id)
                VALUES 
                    ("admin1@mail.de","admin","00000","admintown","015937839",1,true,1),
                    ("admin2@mail.de","admin","00000","admintown","015937839",1,true,1);');
    }

    public function tearDown(): void
    {
        //nuke the db
        self::$pdo->exec("DROP DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");
    }
}
