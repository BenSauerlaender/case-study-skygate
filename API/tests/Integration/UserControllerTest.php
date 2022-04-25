<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlConnector;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ArrayIsEmptyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidTypeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\UnsupportedFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ValidationException;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use BenSauer\CaseStudySkygateApi\Utilities\Validator;
use Dotenv\Exception\InvalidFileException;
use PHPUnit\Framework\TestCase;

/**
 * Integration Tests for the UserController
 * 
 * Testing:
 *  -user UserController
 * 
 *  -MySqlConnector
 *  -MySqlCreator
 *  
 *  -MySqlUserAccessor
 *  -MySqlUserAccessor
 *  -MySqlUserAccessor
 * 
 *  -Validator
 *  -SecurityUtilities
 */
final class UserControllerTest extends TestCase
{
    /**
     * The user controller to be tested
     *
     * @var ?UserControllerInterface
     */
    private static ?UserControllerInterface $userController;

    public static function setUpBeforeClass(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../test.env");
        $dotenv->load();

        //get the pdo connection
        $pdo = MySqlConnector::getConnection();

        //reset the DB
        $pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE'] . ";");
        $pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE'] . ";");
        $pdo->exec("use " . $_ENV['DB_DATABASE'] . ";");

        //creates tables
        MySqlTableCreator::create($pdo);


        $pdo->exec('
            INSERT INTO role
                (name)
            VALUES 
                ("user"),
                ("admin"),
                ("test");
        ');

        //setUp the userController
        self::$userController = new UserController(
            new SecurityUtilities(),
            new Validator(),
            new MySqlUserAccessor($pdo),
            new MySqlRoleAccessor($pdo),
            new MySqlEcrAccessor($pdo),
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::$userController = null;
        MySqlConnector::closeConnection();
    }

    /**
     * Tests if the user creation throws Validation Exception by an invalid field-array
     * 
     * @dataProvider invalidCreateFieldArrayProvider
     */
    public function testCreateUserFailsOnInvalidFieldData(array $fields, string $exception, string $msg): void
    {
        $this->expectException(ValidationException::class);
        $this->expectException($exception);
        $this->expectExceptionMessage($msg);

        self::$userController->createUser($fields);
    }

    /**
     * Tests if the user creation succeeds
     */
    public function testCreateFirstUser(): array
    {
        $response = self::$userController->createUser(
            [
                "email"     => "myEmail@mail.de",
                "name"      => "myName",
                "postcode"  => "12345",
                "city"      => "myCity",
                "phone"     => "123456789",
                "password"  => "MyPassword1"
            ]
        );
        $this->assertArrayHasKey("id", $response);
        $this->assertIsInt($response["id"]);
        $this->assertEquals(1, $response["id"]);

        $this->assertArrayHasKey("verificationCode", $response);
        $this->assertIsString($response["verificationCode"]);
        $this->assertEquals(10, strlen($response["verificationCode"]));

        return $response;
    }

    /**
     * test if verifyUser returns false if the code is wrong.
     * 
     * @depends testCreateFirstUser
     */
    public function testVerifyFailsOnWrongUser(array $res): void
    {
        $this->expectException(UserNotFoundException::class);

        self::$userController->verifyUser(101, "123456");
    }

    /**
     * test if verifyUser returns false if the code is wrong.
     * 
     * @depends testCreateFirstUser
     */
    public function testVerifyFailsOnWrongCode(array $res): void
    {
        $ret = self::$userController->verifyUser($res["id"], "123456");
        $this->assertFalse($ret);
    }

    /**
     * test to verify a just created user.
     * 
     * @depends testCreateFirstUser
     */
    public function testVerifyFirstUser(array $res): array
    {
        $ret = self::$userController->verifyUser($res["id"], $res["verificationCode"]);
        $this->assertTrue($ret);

        return $res;
    }

    /**
     * test if verifyUser returns false if the code is wrong.
     * 
     * @depends testVerifyFirstUser
     */
    public function testVerifyFailsOnSameUser(array $res): void
    {
        $this->expectException(BadMethodCallException::class);

        self::$userController->verifyUser($res["id"], $res["verificationCode"]);
    }

    /**
     * Tests if the creation fails if the email is already taken
     * @depends testCreateFirstUser
     */
    public function testCreateUserFailsOnSameEmail(): void
    {

        $this->expectException(DuplicateEmailException::class);

        self::$userController->createUser(
            [
                "email"     => "myEmail@mail.de",
                "name"      => "myName",
                "postcode"  => "12345",
                "city"      => "myCity",
                "phone"     => "123456789",
                "password"  => "MyPassword1"
            ]
        );
    }

    /**
     * test if second user is created and verified correctly
     * 
     * @depends testVerifyFirstUser
     */
    public function testCreateAndVerifySecondUser(): void
    {

        $response = self::$userController->createUser(
            [
                "email"     => "yourEmail@mail.de",
                "name"      => "yourName",
                "postcode"  => "54321",
                "city"      => "yourCity",
                "phone"     => "987654321",
                "password"  => "yourPassword1",
                "role"      => "admin"
            ]
        );

        $ret = self::$userController->verifyUser($response["id"], $response["verificationCode"]);
        $this->assertTrue($ret);
    }

    /**
     * Tests if update throws exception on various situations
     * 
     * @depends testCreateAndVerifySecondUser
     * @dataProvider invalidUpdateFieldArrayProvider
     */
    public function testUpdateFirstUserFails(int $id, array $fields, string $exception): void
    {
        $this->expectException($exception);

        self::$userController->updateUser($id, $fields);
    }

    /**
     * Tests if update throws exception on various situations
     * 
     * @depends testCreateFirstUser
     */
    public function testUpdateFirstUser(): void
    {
        self::$userController->updateUser(1, [
            "name"      => "myNewName",
            "postcode"  => "11111",
            "city"      => "yourCity",
            "phone"     => "111111111",
            "role"      => "admin"
        ]);
    }

    /**
     * Tests if updatePassword throws exception if user not found
     * 
     * @depends testCreateFirstUser
     */
    public function testUpdatePassFailsOnInvalidUser(): void
    {
        $this->expectException(UserNotFoundException::class);
        self::$userController->updateUsersPassword(10, "new", "old");
    }

    /**
     * Tests if updatePassword throws exception if the new password is invalid
     * 
     * @depends testCreateFirstUser
     */
    public function testUpdatePassFailsOnInvalidPass(): void
    {
        $this->expectException(InvalidFieldException::class);
        self::$userController->updateUsersPassword(1, "incorrect", "MyPassword1");
    }

    /**
     * Tests if updatePassword returns false if the old password is incorrect
     *
     * @depends testCreateFirstUser
     */
    public function testUpdatePassFailsOnIncorrectPass(): void
    {
        $ret = self::$userController->updateUsersPassword(1, "MyPassword2", "notMyPassword");
        $this->assertFalse($ret);
    }

    /**
     * Tests updatePassword 
     *
     * @depends testCreateFirstUser
     */
    public function testUpdatePass(): void
    {
        $ret = self::$userController->updateUsersPassword(1, "MyPassword2", "MyPassword1");
        $this->assertTrue($ret);
    }

    /**
     * Tests if verifyUsersEmailChange throws exception no request found
     * 
     * @depends testCreateFirstUser
     */
    public function testVerifyEcrNotFound(): void
    {
        $this->expectException(EcrNotFoundException::class);
        self::$userController->verifyUsersEmailChange(1, "code");
    }

    /**
     * Tests if requestUsersEmailChange throws exception if user not found
     * 
     * @depends testCreateFirstUser
     */
    public function testRequestEcrFailsOnInvalidUser(): void
    {
        $this->expectException(UserNotFoundException::class);
        self::$userController->requestUsersEmailChange(10, "myNewEmail@mail.de");
    }

    /**
     * Tests if requestUsersEmailChange throws exception if email not free
     * 
     * @depends testCreateFirstUser
     */
    public function testRequestEcrFailsOnDuplicateEmail(): void
    {
        $this->expectException(DuplicateEmailException::class);
        self::$userController->requestUsersEmailChange(1, "yourEmail@mail.de");
    }

    /**
     * Tests if requestUsersEmailChange throws exception if email is invalid
     * 
     * @depends testCreateFirstUser
     */
    public function testRequestEcrFailsOnInvalidEmail(): void
    {
        $this->expectException(InvalidFieldException::class);
        self::$userController->requestUsersEmailChange(1, "invalidEmail");
    }

    /**
     * Tests if requestUsersEmailChange works
     * 
     * @depends testCreateFirstUser
     */
    public function testRequestEcr(): string
    {
        $response = self::$userController->requestUsersEmailChange(1, "myNewEmail@mail.de");

        $this->assertIsString($response);
        $this->assertEquals(10, strlen($response));

        return $response;
    }

    /**
     * Tests if verifyUsersEmailChange throws exception if the code is wrong
     * 
     * @depends testRequestEcr
     */
    public function testVerifyEcrFailsOnIncorrectCode(): void
    {
        $response = self::$userController->verifyUsersEmailChange(1, "code");
        $this->assertFalse($response);
    }

    /**
     * Tests if verifyUsersEmailChange throws exception if the code is wrong
     * 
     * @depends testRequestEcr
     */
    public function testVerifyEcr(string $code): void
    {
        $response = self::$userController->verifyUsersEmailChange(1, $code);
        $this->assertTrue($response);
    }

    /**
     * test if deletion throws exception if user not exists
     */
    public function testDeleteUserNotFound()
    {
        $this->expectException(UserNotFoundException::class);
        self::$userController->deleteUser(10);
        //after ecr
    }

    /**
     * test if deletion works correctly
     * 
     * @depends testRequestEcr
     */
    public function testDeleteFirstUser()
    {
        self::$userController->deleteUser(1);
        //after ecr 
    }


    public function invalidUpdateFieldArrayProvider(): array
    {
        return [
            "invalid userID" => [
                10, ["name" => "newName"], UserNotFoundException::class
            ],
            "empty array" => [
                1, [], ArrayIsEmptyException::class
            ],
            "unsupported field" => [
                1, ["quatsch" => "quatsch"], UnsupportedFieldException::class
            ],
            "invalid type" => [
                1, ["name" => 123], InvalidTypeException::class
            ],
            "invalid field" => [
                1, ["name" => "1!"], InvalidFieldException::class
            ],
            "invalid role" => [
                1, ["role" => "quatsch"], RoleNotFoundException::class
            ],
        ];
    }

    public function invalidCreateFieldArrayProvider(): array
    {
        return [
            "missing email and name" => [
                [
                    "postcode"  => "myPostcode",
                    "city"      => "myCity",
                    "phone"     => "myPhone",
                    "password"  => "MyPassword",
                    "role"      => "myRole"
                ], RequiredFieldException::class, "Missing fields: email,name"
            ],
            "unsupported field" => [
                [
                    "quatsch"     => "",
                    "email"     => "myEmail",
                    "name"      => "myName",
                    "postcode"  => "myPostcode",
                    "city"      => "myCity",
                    "phone"     => "myPhone",
                    "password"  => "MyPassword",
                    "role"      => "myRole"
                ], UnsupportedFieldException::class, "Field: quatsch"
            ],
            "invalid type" => [
                [
                    "email"     => "myEmail@mail.de",
                    "name"      => 123,
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "MyPassword1",
                    "role"      => "myRole"
                ], InvalidTypeException::class, "name"
            ],
            "invalid email and password" => [
                [
                    "email"     => "myEmail",
                    "name"      => "myName",
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "mypassword",
                ], InvalidFieldException::class, "Invalid fields with reasons: email=NO_EMAIL,password=NO_UPPER_CASE+NO_NUMBER"
            ]
        ];
    }
}
