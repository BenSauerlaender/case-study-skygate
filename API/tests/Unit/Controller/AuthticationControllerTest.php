<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\Controller\AuthenticationController;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use ReallySimpleJWT\Token;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for the AuthenticationController
 */
final class AuthenticationControllerTest extends TestCase
{
    private ?UserAccessorInterface $userAccessorMock = null;
    private ?RefreshTokenAccessorInterface $rtAccessorMock = null;
    private ?RoleAccessorInterface $roleAccessorMock = null;

    private ?AuthenticationControllerInterface $authController = null;

    private static $refreshSecret = "";


    public static function setUpBerforeClass(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../../test.env");
        $dotenv->load();
        self::$refreshSecret = $_ENV["REFRESH_TOKEN_SECRET"];
    }

    public function setUp(): void
    {
        $this->userAccessorMock = $this->createMock(UserAccessorInterface::class);
        $this->rtAccessorMock = $this->createMock(RefreshTokenAccessorInterface::class);
        $this->roleAccessorMock = $this->createMock(RoleAccessorInterface::class);
        $this->authController = new AuthenticationController($this->ucMock, $this->rtAccessorMock, $this->roleAccessorMock);
    }

    public function tearDown(): void
    {
        $this->userAccessorMock = null;
        $this->rtAccessorMock = null;
        $this->roleAccessorMock = null;
        $this->authController  = null;
    }

    /**
     * Tests if the method throws the correct exception if the user with the specified userID is not there
     */
    public function testGetNewRefreshTokenOnInvalidUserID(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->userAccessorMock
            ->expect($this->once())
            ->method("get")
            ->with($this->equalTo(0))
            ->will($this->throwException(new UserNotFoundException()));

        $this->authController->getNewRefreshToken(0);
    }

    /**
     * Tests if the method returns a valid token with the correct payload
     */
    public function testGetNewRefreshTokenSuccessful(): void
    {
        $this->userAccessorMock
            ->expect($this->once())
            ->method("get")
            ->with($this->equalTo(0))
            ->willReturn(["roleID" => 1]);

        $this->roleAccessorMock
            ->expect($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["permissions" => "permission123"]);

        $this->rtAccessorMock
            ->expect($this->once())
            ->method("increaseCount")
            ->with($this->equalTo(0));

        $this->rtAccessorMock
            ->expect($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(0))
            ->willReturn(11);

        $ret = $this->authController->getNewRefreshToken(0);

        //expect token is valid
        Token::validate($ret, self::$refreshSecret);


        $payload = Token::getPayLoad($ret);
        $this->assertEquals(11, $payload["cnt"]);
        $this->assertEquals(0, $payload["id"]);
        $this->assertEquals("permission123", $payload["perm"]);
    }
}
