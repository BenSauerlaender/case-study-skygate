<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\Controller\AuthenticationController;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use InvalidArgumentException;
use ReallySimpleJWT\Token;
use PHPUnit\Framework\TestCase;
use ReallySimpleJWT\Decode;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Parse;

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
    private static $accessSecret = "";


    public static function setUpBerforeClass(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../../test.env");
        $dotenv->load();
        self::$refreshSecret = $_ENV["REFRESH_TOKEN_SECRET"];
        self::$accessSecret = $_ENV["ACCESS_TOKEN_SECRET"];
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

        $this->rtAccessorMock
            ->expect($this->once())
            ->method("increaseCount")
            ->with($this->equalTo(0))
            ->will($this->throwException(new UserNotFoundException()));

        $this->authController->getNewRefreshToken(0);
    }

    /**
     * Tests if the method returns a valid token with the correct payload
     */
    public function testGetNewRefreshTokenSuccessful(): void
    {

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

        $jwt = new Jwt($ret);
        $parse = new Parse($jwt, new Decode());
        $parsed = $parse->parse();
        $this->assertTrue(abs($parsed->getExpiration() - (time() + 60 * 60 * 24 * 30)) <= 10);
    }

    /**
     * Tests if the method throws the correct exception if the string is no jwt
     */
    public function testGetNewAccessTokenWithInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->authController->getNewAccessToken("");
    }

    /**
     * Tests if the method throws the correct exception if the refreshToken is expired
     */
    public function testGetNewAccessTokenWithExpiredRefreshToken(): void
    {
        $this->expectException(ExpiredTokenException::class);

        $userId = 12;
        $expiration = time() - 1;
        $issuer = 'localhost';

        $token = Token::create($userId, self::$refreshSecret, $expiration, $issuer);

        $this->authController->getNewAccessToken($token);
    }

    /**
     * Tests if the method throws the correct exception if the refreshToken is invalid
     */
    public function testGetNewAccessTokenWithInvalidRefreshToken(): void
    {
        $this->expectException(InvalidTokenException::class);

        $payload = [
            'exp' => time() + 100,
            'id'  => 1,
            'cnt' => 12
        ];

        $this->rtAccessorMock
            ->expect($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(1))
            ->willReturn(13);

        $token = Token::customPayload($payload, self::$refreshSecret);

        $this->authController->getNewAccessToken($token);
    }

    /**
     * Tests if the method throws the correct exception if the refreshToken is invalid
     */
    public function testGetNewAccessTokenSuccessful(): void
    {
        $this->rtAccessorMock
            ->expect($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(1))
            ->willReturn(12);

        $this->userAccessorMock
            ->expect($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["roleID" => 2]);

        $this->roleAccessorMock
            ->expect($this->once())
            ->method("get")
            ->with($this->equalTo(2))
            ->willReturn(["permissions" => "permission123"]);

        $payload = [
            'exp' => time() + 100,
            'id'  => 1,
            'cnt' => 12
        ];

        $token = Token::customPayload($payload, self::$refreshSecret);

        $ret = $this->authController->getNewAccessToken($token);

        //expect token is valid
        Token::validate($ret, self::$accessSecret);


        $payload = Token::getPayLoad($ret);
        $this->assertEquals(1, $payload["id"]);
        $this->assertEquals("permission123", $payload["perm"]);

        $jwt = new Jwt($ret);
        $parse = new Parse($jwt, new Decode());
        $parsed = $parse->parse();
        $this->assertTrue(abs($parsed->getExpiration() - (time() + 60 * 15)) <= 10);
    }

    /**
     * Tests if the method throws the correct exception if the string is no jwt
     */
    public function testAuthenticateAccessTokenWithInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->authController->authenticateAccessToken("");
    }

    /**
     * Tests if the method throws the correct exception if the refreshToken is expired
     */
    public function testAuthenticateAccessTokenWithExpiredRefreshToken(): void
    {
        $this->expectException(ExpiredTokenException::class);

        $userId = 12;
        $expiration = time() - 1;
        $issuer = 'localhost';

        $token = Token::create($userId, self::$refreshSecret, $expiration, $issuer);

        $this->authController->authenticateAccessToken($token);
    }

    /**
     * Tests if the method returns the correct array 
     */
    public function testAuthenticateAccessTokenSuccessful(): void
    {
        $payload = [
            'exp' => time() + 100,
            'id'  => 1,
            'permission' => "perm123"
        ];

        $token = Token::customPayload($payload, self::$refreshSecret);

        $ret = $this->authController->authenticateAccessToken($token);
        $this->assertEquals(["id" => 1, "permissions" => "perm123"], $ret);
    }
}
