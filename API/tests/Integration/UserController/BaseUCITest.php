<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration\UserController;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\SecurityController;
use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\Utilities\DbConnector;
use BenSauer\CaseStudySkygateApi\Utilities\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\Controller\ValidationController;
use PHPUnit\Framework\TestCase;

/**
 * Base test suite for all UserControllerIntegration (UCI) Tests 
 */
abstract class BaseUCITest extends TestCase
{
    /**
     * The user controller to be tested
     *
     * @var ?UserControllerInterface
     */
    protected ?UserControllerInterface $userController;

    public function setUp(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../../test.env");
        $dotenv->load();

        //get the pdo connection
        $pdo = DbConnector::getConnection();

        //reset the DB
        $pdo->exec("DROP DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        $pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        $pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");

        //creates tables
        MySqlTableCreator::create($pdo);

        //insert 3 roles
        $pdo->exec('
            INSERT INTO role
                (name)
            VALUES 
                ("user"),
                ("admin"),
                ("test");
        ');

        //setUp the userController
        $this->userController = new UserController(
            new SecurityController(),
            new ValidationController(),
            new MySqlUserAccessor($pdo),
            new MySqlRoleAccessor($pdo),
            new MySqlEcrAccessor($pdo),
        );
    }

    public function tearDown(): void
    {
        $this->userController = null;
        DbConnector::closeConnection();
    }

    /**
     * Creates an user and returns verificationCode
     * 
     * @return string VerificationCode
     */
    protected function createUser(): string
    {
        $res = $this->userController->createUser(
            [
                "email"     => "myEmail@mail.de",
                "name"      => "myName",
                "postcode"  => "12345",
                "city"      => "myCity",
                "phone"     => "123456789",
                "password"  => "MyPassword1",
                "role"  => "user"
            ]
        );
        return $res["verificationCode"];
    }

    /**
     * Creates 2 users
     */
    protected function create2Users(): void
    {
        $this->createUser();

        $this->userController->createUser(
            [
                "email"     => "yourEmail@mail.de",
                "name"      => "yourName",
                "postcode"  => "54321",
                "city"      => "yourCity",
                "phone"     => "987654321",
                "password"  => "YourPassword1",
                "role"  => "admin"
            ]
        );
    }

    /**
     * Creates an user and request an emailChange
     * 
     * @return string The code to verify the ecr 
     */
    protected function createUserWithEcr(): string
    {
        $this->createUser();

        return $this->userController->requestUsersEmailChange(1, "myNewEmail@mail.de");
    }
}
