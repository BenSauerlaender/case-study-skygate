<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors\EcrAccessor;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors\BaseMySqlAccessorTest;
use InvalidArgumentException;
use PDO;

/**
 * Base class for all MySqlAccessor tests
 * 
 * Handles the database connection
 */
final class ECRAccTest extends BaseMySqlAccessorTest
{
    private ?EcrAccessorInterface $accessor;

    public function setUp(): void
    {
        self::resetDB();

        //creates a role
        self::$pdo->exec('
            INSERT INTO role
                (role_id, name)
            VALUES 
                (0,"test");
        ');

        //creates 2 users
        self::$pdo->exec('
            INSERT INTO user
                (email, name, postcode, city, phone, hashed_pass, verified, role_id)
            VALUES 
                ("user0@mail.de","user0","00000","admintown","015937839",1,true,0),
                ("user1@mail.de","user1","00000","admintown","015937839",1,true,0),
                ("user2@mail.de","user2","00000","admintown","015937839",1,true,0);
        ');

        //creates 2 requests
        self::$pdo->exec('
            INSERT INTO emailChangeRequest 
                (user_id,new_email,verification_code)
            VALUES
                (1,"newEmailFor1","code"),
                (0,"newEmailFor0","code2");
        ');

        $this->startChangedRowsObservation();

        //initialize the EcrAccessor
        $this->accessor = new MySqlEcrAccessor(self::$pdo);
    }

    /**
     * Tests if the method returns null if the userID don't exists
     */
    public function testFindByUserIDWhenRequestNotExists(): void
    {
        $response = $this->accessor->findByUserID(3);
        $this->assertNull($response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method returns the correct id
     * 
     * @dataProvider successIDProvider
     */
    public function testFindByUserIDSuccessful(int $userID, int $ecrID): void
    {
        $response = $this->accessor->findByUserID($userID);
        $this->assertEquals($ecrID, $response);

        $this->assertChangedRowsEquals(0);
    }

    public static function successIDProvider(): array
    {
        return [
            [1, 0],
            [0, 1]
        ];
    }

    /**
     * Tests if the method returns null if the email don't exists
     */
    public function testFindByEmailWhenRequestNotExists(): void
    {
        $response = $this->accessor->findByEmail("some@email.de");
        $this->assertNull($response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method returns the correct id
     * 
     * @dataProvider successEmailProvider
     */
    public function testFindByEmailSuccessful(string $email, int $ecrID): void
    {
        $response = $this->accessor->findByEmail($email);
        $this->assertEquals($ecrID, $response);

        $this->assertChangedRowsEquals(0);
    }

    public static function successEmailProvider(): array
    {
        return [
            ["newEmailFor1", 0],
            ["newEmailFor0", 1]
        ];
    }

    /**
     * Tests if the method throws an exception if there is no request with the specified id
     */
    public function testDeleteFailsOnInvalidID(): void
    {

        $this->expectException(InvalidArgumentException::class);

        $this->accessor->delete(5);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method correctly deletes
     */
    public function testDeleteSuccessful(): void
    {
        $this->accessor->delete(0);

        $emails = self::$pdo->query('SELECT new_email from emailChangeRequest')->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals(["newEmailFor0"], $emails);

        $this->assertChangedRowsEquals(1);
    }


    /**
     * Tests if the method throws an exception if there is no request from the specified id
     */
    public function testDeleteByUserIDFailsOnInvalidID(): void
    {

        $this->expectException(InvalidArgumentException::class);

        $this->accessor->deleteByUserID(5);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method correctly deletes
     */
    public function testDeleteByUserIDSuccessful(): void
    {
        $this->accessor->deleteByUserID(0);

        $emails = self::$pdo->query('SELECT new_email from emailChangeRequest')->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals(["newEmailFor1"], $emails);

        $this->assertChangedRowsEquals(1);
    }

    /**
     * Tests if the insert throws an exception if the user has already a request
     */
    public function testInsertFailsOnDuplicateUser(): void
    {

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("userID");

        $this->accessor->insert(0, "neu", "code");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the insert throws an exception if the email is already in the table
     */
    public function testInsertFailsOnDuplicateEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("newEmail");

        $this->accessor->insert(2, "newEmailFor0", "code");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the insert throws an exception if the userID dont exists
     */
    public function testInsertFailsWhenUserNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("don't exists");

        $this->accessor->insert(3, "newE", "code");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method successful inserts a new request
     */
    public function testInsertSuccessful(): void
    {

        $this->accessor->insert(2, "newEmailFor2", "code");

        $this->assertChangedRowsEquals(1);

        $row = self::$pdo->query('
            SELECT request_id, user_id, new_email, verificationCode 
            FROM emailChangeRequest
            WHERE user_id=2
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([["request_id" => 2, "user_id" => 2, "new_email" => "newEmailFor2", "verification_code" => "code"]], $row);
    }

    /**
     * Tests if the method returns null if no request with that id exists
     */
    public function testGetReturnsNullIfNoRequestWithID(): void
    {
        $response = $this->accessor->get(2);

        $this->assertNull($response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method works correctly
     */
    public function testGetSuccessful(): void
    {
        $response = $this->accessor->get(0);

        $this->assertEquals(["newEmail" => "newEmailFor1", "verificationCode" => "code"], $response);

        $this->assertChangedRowsEquals(0);
    }
}
