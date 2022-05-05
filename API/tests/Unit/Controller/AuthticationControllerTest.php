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
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use ReallySimpleJWT\Token;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for the AuthenticationController
 */
final class AuthenticationControllerTest extends TestCase
{
    private ?UserControllerInterface $ucMock = null;
    private ?RefreshTokenAccessorInterface $rtAccessorMock = null;
    private ?RoleAccessorInterface $roleAccessorMock = null;

    private ?AuthenticationControllerInterface $authController = null;

    private static $refreshSecret = "";


    public static function setUpBerforeClass():void{
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../../test.env");
        $dotenv->load();
        self::$refreshSecret = $_ENV["REFRESH_TOKEN_SECRET"];
    }

    public function setUp(): void
    {
        $this->ucMock = $this->createMock(UserControllerInterface::class);
        $this->rtAccessorMock = $this->createMock(RefreshTokenAccessorInterface::class);
        $this->roleAccessorMock = $this->createMock(RoleAccessorInterface::class);
        $this->authController = new AuthenticationController($this->ucMock, $this->rtAccessorMock, $this->roleAccessorMock);
    }

    public function tearDown(): void
    {
        $this->ucMock = null;
        $this->rtAccessorMock = null;
        $this->roleAccessorMock = null;
        $this->authController  = null;
    }

    /**
     * Tests if the method throws the right exception if the user with the specified userID is not there
     */
    public function testGetNewRefreshTokenOnInvalidUserID(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->rtAccessorMock
            ->expect($this->once())
            ->method("increaseCount")
            ->will($this->throwException(new UserNotFoundException()));

        $this->authController->getNewRefreshToken(0);
    }

    /**
     * Tests if the method throws the right exception if the user with the specified userID is not there
     */
    public function testGetNewRefreshTokenSuccessful(): void
    {
        $this->rtAccessorMock
            ->expect($this->once())
            ->method("increaseCount")
            ->with($this->equalTo(0))
            ->will($this->throwException(new UserNotFoundException()));

        $this->rtAccessorMock
            ->expect($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(0))
            ->willReturn(11);

        $ret = $this->authController->getNewRefreshToken(0);

        //expect token is valid
        Token::validate($ret, self::$refreshSecret)

    }
}
