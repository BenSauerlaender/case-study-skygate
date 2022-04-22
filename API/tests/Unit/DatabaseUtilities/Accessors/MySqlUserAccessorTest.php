<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\Accessors\EcrAccessor;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\Accessors\BaseMySqlAccessorTest;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ArrayIsEmptyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidTypeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\UnsupportedFieldException;
use PDO;

/**
 * Test class for the MySqlUserAccessor 
 */
final class MySqlUserAccessorTest extends BaseMySqlAccessorTest
{
    private ?UserAccessorInterface $accessor;

    public function setUp(): void
    {
        self::resetDB();

        //creates a role
        self::$pdo->exec('INSERT INTO role (name) VALUES ("test"),("test2");');

        //creates 3 users
        self::$pdo->exec('
            INSERT INTO user
                (email, name, postcode, city, phone, hashed_pass, verified, role_id)
            VALUES 
                ("user1@mail.de","user1","00000","admintown","015937839","1",true,1),
                ("user2@mail.de","user2","00000","admintown","015937839","1",true,1),
                ("user3@mail.de","user3","00000","admintown","015937839","1",true,1);
        ');

        $this->startChangedRowsObservation();

        //initialize the EcrAccessor
        $this->accessor = new MySqlUserAccessor(self::$pdo);
    }

    /**
     * Tests if the insertion throws an exception if the email is already taken
     */
    public function testInsertFailsByDuplicateEmail(): void
    {
        $this->expectException(DuplicateEmailException::class);
        $this->expectExceptionMessage("user1");

        $this->accessor->insert("user1@mail.de", "user4", "00000", "city", "0123", "1", true, null, 1);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the insertion throws an exception if the role dont exists
     */
    public function testInsertFailsByInvalidRole(): void
    {
        $this->expectException(RoleNotFoundException::class);
        $this->expectExceptionMessage("3");

        $this->accessor->insert("user4@mail.de", "user4", "00000", "city", "0123", "1", true, null, 3);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the insertion works successful
     */
    public function testInsertSuccessful(): void
    {
        $this->accessor->insert("user4@mail.de", "user4", "00000", "city", "0123", "1", true, null, 1);

        $this->assertChangedRowsEquals(1);

        $row = self::$pdo->query('
            SELECT user_id, email, name, postcode, city, phone, hashed_pass, verified, verification_code, role_id
            FROM user
            WHERE email="user4@mail.de";
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([[
            "user_id" => 4,
            "email" => "user4@mail.de",
            "name" => "user4",
            "postcode" => "00000",
            "city" => "city",
            "phone" => "0123",
            "hashed_pass" => "1",
            "verified" => 1,
            "verification_code" => null,
            "role_id" => 1
        ]], $row);
    }

    /**
     * Tests if the method throws an exception if there is no user with this id
     */
    public function testDeleteFailsByInvalidUserID(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("10");

        $this->accessor->delete(10);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the deletion method works correctly
     */
    public function testDeleteSuccessful(): void
    {
        $this->accessor->delete(2);

        $this->assertChangedRowsEquals(1);

        $names = self::$pdo->query('
            SELECT  name
            FROM user;
        ')->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals(["user1", "user3"], $names);
    }

    /**
     * Tests if the method throws an exception if there is no user with this id
     */
    public function testUpdateFailsOnInvalidID(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("10");

        $this->accessor->update(10, ["name" => "Klaus"]);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws an exception if the field array is empty
     */
    public function testUpdateFailsOnEmptyArray(): void
    {
        $this->expectException(ArrayIsEmptyException::class);

        $this->accessor->update(1, []);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws an exception if the field array has at least one invalid key
     */
    public function testUpdateFailsOnInvalidKey(): void
    {
        $this->expectException(UnsupportedFieldException::class);
        $this->expectExceptionMessage("quatsch");

        $this->accessor->update(1, ["name" => "Klaus", "quatsch" => "q"]);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws an exception if the field array has at least one invalid field type
     */
    public function testUpdateFailsOnInvalidType(): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage("email");

        $this->accessor->update(1, ["name" => "Klaus", "email" => 123]);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws an exception if the email to update is already taken
     */
    public function testUpdateFailsOnDuplicateEmail(): void
    {
        $this->expectException(DuplicateEmailException::class);
        $this->expectExceptionMessage("user2@mail.de");

        $this->accessor->update(1, ["name" => "Klaus", "email" => "user2@mail.de"]);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws an exception if the role dont exists
     */
    public function testUpdateFailsOnInvalidRole(): void
    {
        $this->expectException(RoleNotFoundException::class);
        $this->expectExceptionMessage("10");

        $this->accessor->update(1, ["name" => "Klaus", "roleID" => 10]);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method works correctly
     */
    public function testUpdateSuccessful(): void
    {
        $this->accessor->update(1, [
            "email" => "newMail",
            "name" => "newName",
            "postcode" => "12345",
            "city" => "newCity",
            "phone" => "newPhone",
            "hashedPass" => "newPass",
            "verified" => false,
            "verificationCode" => "AAA",
            "roleID" => 2
        ]);

        $this->assertChangedRowsEquals(1);

        $row = self::$pdo->query('
            SELECT email, name, postcode, city, phone, hashed_pass, verified, verification_code, role_id
            FROM user
            WHERE user_id=1;
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([[
            "email" => "newMail",
            "name" => "newName",
            "postcode" => "12345",
            "city" => "newCity",
            "phone" => "newPhone",
            "hashed_pass" => "newPass",
            "verified" => 0,
            "verification_code" => "AAA",
            "role_id" => 2
        ]], $row);
    }

    /**
     * Tests if the method returns null if there is no user with this email
     */
    public function testFindByEmailCantFind(): void
    {
        $response = $this->accessor->findByEmail("noEmail");

        $this->assertNull($response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method works correctly
     */
    public function testFindByEmailCorrectly(): void
    {
        $response = $this->accessor->findByEmail("user1@mail.de");

        $this->assertEquals(1, $response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws an exception if there is no user with this id.
     */
    public function testGetFailsIfUserDontExists()
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("10");

        $this->accessor->get(10);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method works correctly.
     */
    public function testGetSuccessful()
    {
        $response = $this->accessor->get(1);

        $this->assertChangedRowsEquals(0);

        //check if created and updated are there. than remove them.
        $this->assertArrayHasKey("createdAt", $response);
        $this->assertArrayHasKey("updatedAt", $response);
        $response = array_diff_key($response, ["updatedAt" => "", "createdAt" => ""]);

        $this->assertEquals([
            "id"                => 1,
            "email"             => "user1@mail.de",
            "name"              => "user1",
            "postcode"          => "00000",
            "city"              => "admintown",
            "phone"             => "015937839",
            "roleID"            => 1,
            "hashedPass"        => "1",
            "verified"         => true,
            "verificationCode"  => null
        ], $response);
    }
}
