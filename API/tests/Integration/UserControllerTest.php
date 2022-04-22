<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlConnector;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\UnsupportedFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ValidationException;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use BenSauer\CaseStudySkygateApi\Utilities\Validator;
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
     * @dataProvider invalidFieldArrayProvider
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
     * 
     */
    public function testCreateFirstUser(): void
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
    }

    /*TODO next: 
        - verify user fails /succeed 
        - try to verify again
        - try to add second user with same email
        - get users data (check)

*/

    public function invalidFieldArrayProvider(): array
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
