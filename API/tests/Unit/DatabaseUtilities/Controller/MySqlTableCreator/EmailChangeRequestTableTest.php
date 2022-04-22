<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\Controller\MySqlTableCreator;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\BaseDatabaseTest;
use PDO;
use PDOException;

/**
 * Testsuit for the ECR table creation from MySqlTableCreator 
 */
final class EmailChangeRequestTableTest extends BaseDatabaseTest
{
    /**
     * Tests if the ecr table was created
     */
    public function testECRTableCreated(): void
    {
        $stmt = self::$pdo->query('
            SHOW TABLES;
        ');

        $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains("emailChangeRequest", $allTables);
    }

    /**
     * Tests if the ecr table has all Columns
     */
    public function testECRTableHasAllColumns(): void
    {
        $stmt = self::$pdo->query('
            DESCRIBE emailChangeRequest;
        ');

        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $expectedColumns = ["request_id", "user_id", "new_email", "verification_code", "created_at", "updated_at"];

        //compares both arrays but ignores the order
        $this->assertEqualsCanonicalizing($expectedColumns, $columns);
    }

    /**
     * Tests if a table insert only works with all necessary values
     * 
     * @dataProvider incompleteInsertProvider
     */
    public function testECRInsertFailsWithoutAllNecessaryValues(string $insert): void
    {
        $this->insertUser();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("doesn't have a default value");

        self::$pdo->exec($insert);
    }

    public function incompleteInsertProvider(): array
    {

        return [
            "missing user_id" => ['
                INSERT INTO  emailChangeRequest
                    (new_email, verification_code)
                VALUES 
                    ("new@email.de","1234");
            '],
            "missing new_email" => ['
                INSERT INTO  emailChangeRequest
                    (user_id,  verification_code)
                VALUES 
                    (0,"1234");
            '],
            "missing verification_code" => ['
                INSERT INTO  emailChangeRequest
                    (user_id, new_email)
                VALUES 
                    (0,"new@email.de");
            '],
        ];
    }

    /**
     * Tests if the insert fails if the specified user_id dont have a corresponding user 
     */
    public function testECRInsertFailsIfUserNotExists(): void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("a foreign key constraint fails");

        self::$pdo->exec('
                INSERT INTO  emailChangeRequest
                    (user_id, new_email, verification_code)
                VALUES 
                    (3,"new2@email.de","1234");');
    }

    /**
     * Tests if the request_id increments automatically
     */
    public function testECRInsertIDAutoIncrement(): void
    {
        $this->insertUser();

        //inserts 2 ECRs
        self::$pdo->exec('
                INSERT INTO  emailChangeRequest
                    (user_id, new_email, verification_code)
                VALUES 
                    (1,"new1@email.de","1234"),
                    (2,"new2@email.de","1234");');

        $response = self::$pdo->query('
            SELECT request_id, user_id FROM  emailChangeRequest;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //check the request_ids
        $this->assertEquals([
            ["request_id" => 1, "user_id" => 1],
            ["request_id" => 2, "user_id" => 2]
        ], $response);
    }

    /**
     * tests if the insert fails, if trying to insert 2 ECRs with the same user_id
     */
    public function testECRInsertFailsByDuplicateUserID(): void
    {
        $this->insertUser();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO  emailChangeRequest
                    (user_id, new_email, verification_code)
                VALUES 
                    (1,"new1@email.de","1234"),
                    (1,"new2@email.de","1234");');
    }


    /**
     * tests if the insert fails, if trying to insert 2 ECRs with the same new_email
     */
    public function testECRInsertFailsByDuplicateNewEmail(): void
    {
        $this->insertUser();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO  emailChangeRequest
                    (user_id, new_email, verification_code)
                VALUES 
                    (1,"new@email.de","1234"),
                    (2,"new@email.de","1234");');
    }


    /**
     * tests if the insert fails, if trying to insert 2 ECRs with the same id
     */
    public function testECRInsertFailsByDuplicateID(): void
    {
        $this->insertUser();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage("Duplicate entry");

        self::$pdo->exec('
                INSERT INTO  emailChangeRequest
                    (request_id, user_id, new_email, verification_code)
                VALUES 
                    (1,1,"new1@email.de","1234"),
                    (1,2,"new2@email.de","1234");');
    }

    /**
     * Tests if the created_at and updated_at field is set correctly by an insert
     */
    public function testCreatedAndUpdatedAtIfInserted(): void
    {
        $this->insertUser();

        self::$pdo->exec('
                INSERT INTO  emailChangeRequest
                    (user_id, new_email, verification_code)
                VALUES 
                    (1,"new1@email.de","1234"),
                    (2,"new2@email.de","1234");');

        $time = time();

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM emailChangeRequest;
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
        $this->insertUser();

        //insert user
        self::$pdo->exec('
                INSERT INTO emailChangeRequest
                    (user_id, new_email, verification_code)
                VALUES
                    (1,"new1@email.de","1234");');

        sleep(1);

        //update user
        self::$pdo->exec(' UPDATE  emailChangeRequest SET new_email="new2@mail.de" WHERE request_id=1;');

        $response = self::$pdo->query('
            SELECT created_at, updated_at FROM  emailChangeRequest;
        ')->fetchAll(PDO::FETCH_ASSOC);

        //updated_at and created at are not equal
        $this->assertTrue($response[0]["created_at"] !== $response[0]["updated_at"]);
    }

    /**
     * Inserts 1 role and 2 users
     */
    private function insertUser(): void
    {
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
