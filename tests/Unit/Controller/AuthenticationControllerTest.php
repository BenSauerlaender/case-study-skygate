<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Controller;

use Controller\AuthenticationController;
use Controller\Interfaces\AuthenticationControllerInterface;
use DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use DbAccessors\Interfaces\RoleAccessorInterface;
use DbAccessors\Interfaces\UserAccessorInterface;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\TokenExceptions\ExpiredTokenException;
use Exceptions\TokenExceptions\InvalidTokenException;
use InvalidArgumentException;
use ReallySimpleJWT\Token;
use PHPUnit\Framework\TestCase;
use ReallySimpleJWT\Decode;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Parse;

/**
 * Test suite for the AuthenticationController
 */
final class AuthenticationControllerTest extends TestCase
{
    private ?UserAccessorInterface $userAccessorMock = null;
    private ?RefreshTokenAccessorInterface $rtAccessorMock = null;
    private ?RoleAccessorInterface $roleAccessorMock = null;

    private ?AuthenticationControllerInterface $authController = null;

    public function setUp(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../../test.env");
        $dotenv->load();

        $this->userAccessorMock = $this->createMock(UserAccessorInterface::class);
        $this->rtAccessorMock = $this->createMock(RefreshTokenAccessorInterface::class);
        $this->roleAccessorMock = $this->createMock(RoleAccessorInterface::class);
        $this->authController = new AuthenticationController($this->userAccessorMock, $this->rtAccessorMock, $this->roleAccessorMock);
    }

    public function tearDown(): void
    {
        $this->userAccessorMock = null;
        $this->rtAccessorMock = null;
        $this->roleAccessorMock = null;
        $this->authController  = null;
    }

    /**
     * Tests if the method throws the correct exception if the user with the specified email is not there
     */
    public function testGetNewRefreshTokenOnInvalidUserEmail(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->userAccessorMock
            ->expects($this->once())
            ->method("findByEmail")
            ->with("testmail")
            ->willReturn(null);

        $this->authController->getNewRefreshToken("testmail");
    }

    /**
     * Tests if the method returns a valid token with the correct payload
     */
    public function testGetNewRefreshTokenSuccessful(): void
    {
        $this->userAccessorMock
            ->expects($this->once())
            ->method("findByEmail")
            ->with("testmail")
            ->willReturn(0);

        $this->rtAccessorMock
            ->expects($this->once())
            ->method("increaseCount")
            ->with($this->equalTo(0));

        $this->rtAccessorMock
            ->expects($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(0))
            ->willReturn(11);

        $ret = $this->authController->getNewRefreshToken("testmail");

        //expect token is valid
        Token::validate($ret, $_ENV["REFRESH_TOKEN_SECRET"]);


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

        $payload = [
            'exp' => time() + 1,
            'id'  => 12,
            'cnt' => 1
        ];
        $token = Token::customPayload($payload, $_ENV["REFRESH_TOKEN_SECRET"]);

        sleep(2);

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
            ->expects($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(1))
            ->willReturn(13);

        $token = Token::customPayload($payload, $_ENV["REFRESH_TOKEN_SECRET"]);

        $this->authController->getNewAccessToken($token);
    }

    /**
     * Tests if the method throws the correct exception if the user is no more in the database
     */
    public function testGetNewAccessTokenWhenUserIsDeleted(): void
    {
        $this->expectException(UserNotFoundException::class);

        $payload = [
            'exp' => time() + 100,
            'id'  => 1,
            'cnt' => 12
        ];

        $this->rtAccessorMock
            ->expects($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(1))
            ->willReturn(null);

        $token = Token::customPayload($payload, $_ENV["REFRESH_TOKEN_SECRET"]);

        $this->authController->getNewAccessToken($token);
    }


    /**
     * Tests if the method throws the correct exception if the refreshToken is invalid
     */
    public function testGetNewAccessTokenSuccessful(): void
    {
        $this->rtAccessorMock
            ->expects($this->once())
            ->method("getCountByUserID")
            ->with($this->equalTo(1))
            ->willReturn(12);

        $this->userAccessorMock
            ->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["roleID" => 2]);

        $this->roleAccessorMock
            ->expects($this->once())
            ->method("get")
            ->with($this->equalTo(2))
            ->willReturn(["permissions" => "permission123"]);

        $payload = [
            'exp' => time() + 100,
            'id'  => 1,
            'cnt' => 12
        ];

        $token = Token::customPayload($payload, $_ENV["REFRESH_TOKEN_SECRET"]);

        $ret = $this->authController->getNewAccessToken($token);

        //expect token is valid
        Token::validate($ret, $_ENV["ACCESS_TOKEN_SECRET"]);


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
    public function testValidateAccessTokenWithInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->authController->validateAccessToken("");
    }

    /**
     * Tests if the method throws the correct exception if the accessToken is expired
     */
    public function testValidateAccessTokenWithExpiredToken(): void
    {
        $this->expectException(ExpiredTokenException::class);

        $payload = [
            'exp' => time() + 1,
            'id'  => 12,
            'cnt' => 1
        ];
        $token = Token::customPayload($payload, $_ENV["ACCESS_TOKEN_SECRET"]);

        sleep(2);

        $this->authController->validateAccessToken($token);
    }

    /**
     * Tests if the method returns the correct array 
     */
    public function testValidateAccessTokenSuccessful(): void
    {
        $payload = [
            'exp' => time() + 100,
            'id'  => 1,
            'perm' => "perm123"
        ];

        $token = Token::customPayload($payload, $_ENV["ACCESS_TOKEN_SECRET"]);

        $ret = $this->authController->validateAccessToken($token);
        $this->assertEquals(["userID" => 1, "permissions" => ["perm123"]], $ret);
    }
}
