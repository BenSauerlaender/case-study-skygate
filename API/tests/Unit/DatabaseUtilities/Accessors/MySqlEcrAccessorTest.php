<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\Accessors\EcrAccessor;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateUserException;
use BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\Accessors\BaseMySqlAccessorTest;
use PDO;

/**
 * Test class for the MySqlEcrAccessor
 */
final class MySqlEcrAccessorTest extends BaseMySqlAccessorTest
{
    private ?EcrAccessorInterface $accessor;

    public function setUp(): void
    {
        self::resetDB();

        //creates a role
        self::$pdo->exec('
            INSERT INTO role
                (name)
            VALUES 
                ("test");
        ');

        //creates 3 users
        self::$pdo->exec('
            INSERT INTO user
                (email, name, postcode, city, phone, hashed_pass, verified, role_id)
            VALUES 
                ("user0@mail.de","user0","00000","admintown","015937839",1,true,1),
                ("user1@mail.de","user1","00000","admintown","015937839",1,true,1),
                ("user2@mail.de","user2","00000","admintown","015937839",1,true,1);
        ');

        //creates 2 requests
        self::$pdo->exec('
            INSERT INTO emailChangeRequest 
                (user_id,new_email,verification_code)
            VALUES
                (2,"newEmailFor2","code"),
                (1,"newEmailFor1","code2");
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
            [2, 1],
            [1, 2]
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
            ["newEmailFor2", 1],
            ["newEmailFor1", 2]
        ];
    }

    /**
     * Tests if the method throws an exception if there is no request with the specified id
     */
    public function testDeleteFailsOnInvalidID(): void
    {

        $this->expectException(EcrNotFoundException::class);
        $this->expectExceptionMessage("5");

        $this->accessor->delete(5);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method correctly deletes
     */
    public function testDeleteSuccessful(): void
    {
        $this->accessor->delete(1);

        $emails = self::$pdo->query('SELECT new_email from emailChangeRequest')->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals(["newEmailFor1"], $emails);

        $this->assertChangedRowsEquals(1);
    }


    /**
     * Tests if the method throws an exception if there is no request from the specified id
     */
    public function testDeleteByUserIDFailsOnInvalidID(): void
    {

        $this->expectException(EcrNotFoundException::class);
        $this->expectExceptionMessage("5");

        $this->accessor->deleteByUserID(5);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method correctly deletes
     */
    public function testDeleteByUserIDSuccessful(): void
    {
        $this->accessor->deleteByUserID(1);

        $emails = self::$pdo->query('SELECT new_email from emailChangeRequest')->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals(["newEmailFor2"], $emails);

        $this->assertChangedRowsEquals(1);
    }

    /**
     * Tests if the insert throws an exception if the user has already a request
     */
    public function testInsertFailsOnDuplicateUser(): void
    {

        $this->expectException(DuplicateUserException::class);
        $this->expectExceptionMessage("1");

        $this->accessor->insert(1, "neu", "code");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the insert throws an exception if the email is already in the table
     */
    public function testInsertFailsOnDuplicateEmail(): void
    {
        $this->expectException(DuplicateEmailException::class);
        $this->expectExceptionMessage("newEmailFor1");

        $this->accessor->insert(3, "newEmailFor1", "code");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the insert throws an exception if the userID dont exists
     */
    public function testInsertFailsWhenUserNotExists(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("10");

        $this->accessor->insert(10, "newE", "code");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method successful inserts a new request
     */
    public function testInsertSuccessful(): void
    {

        $this->accessor->insert(3, "newEmailFor3", "code");

        $this->assertChangedRowsEquals(1);

        $row = self::$pdo->query('
            SELECT request_id, user_id, new_email, verification_code 
            FROM emailChangeRequest
            WHERE user_id=3
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([["request_id" => 3, "user_id" => 3, "new_email" => "newEmailFor3", "verification_code" => "code"]], $row);
    }

    /**
     * Tests if the method throws an exception if no request with that id exists
     */
    public function testGetFailsIfNoRequestWithID(): void
    {
        $this->expectException(EcrNotFoundException::class);
        $this->expectExceptionMessage("10");

        $this->accessor->get(10);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method works correctly
     */
    public function testGetSuccessful(): void
    {
        $response = $this->accessor->get(1);

        $this->assertEquals(["newEmail" => "newEmailFor2", "verificationCode" => "code"], $response);

        $this->assertChangedRowsEquals(0);
    }
}
