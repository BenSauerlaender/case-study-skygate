<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration\UserController;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlConnector;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use BenSauer\CaseStudySkygateApi\Utilities\Validator;
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
        $pdo = MySqlConnector::getConnection();

        //reset the DB
        $pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE'] . ";");
        $pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE'] . ";");
        $pdo->exec("use " . $_ENV['DB_DATABASE'] . ";");

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
            new SecurityUtilities(),
            new Validator(),
            new MySqlUserAccessor($pdo),
            new MySqlRoleAccessor($pdo),
            new MySqlEcrAccessor($pdo),
        );
    }

    public function tearDown(): void
    {
        $this->userController = null;
        MySqlConnector::closeConnection();
    }

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

    protected function createUserWithEcr(): string
    {
        $this->createUser();

        return $this->userController->requestUsersEmailChange(1, "myNewEmail@mail.de");
    }
}
