<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\Controller\AuthenticationController;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidPermissionsException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
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

    /**
     * Tests that the function throws an Exception if one of the required permission strings is invalid.
     * 
     * @dataProvider invalidPermissionProvider
     */
    public function testHasPermissionsWithInvalidGivenPermissionString(array $perm): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->authController->hasPermissions($perm, ["user:{all}:{all}"]);
    }

    /**
     * Tests that the function throws an Exception if one of the user permission strings is invalid.
     * 
     * @dataProvider invalidPermissionProvider
     */
    public function testHasPermissionsWithInvalidRequiredPermissionString(array $perm): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->authController->hasPermissions(["user:{all}:{all}"], $perm);
    }

    public function invalidPermissionProvider(): array
    {
        return [
            [["test1"]],
            [["{all}:{all}:{all}:{all}"]],
            [["quatsch:{all}:{all}"]],
            [["{all}:quatsch:{all}"]],
            [["{all}:{all}:quatsch"]],
            [[":{all}:{all}"]],
            [["{all}::{all}"]],
        ];
    }

    /**
     * Tests that the function returns the correct value on multiple scenarios.
     * 
     * @dataProvider permissionProvider
     */
    public function testHasPermissions(array $required, array $given, $hasPerm): void
    {
        $ret = $this->authController->hasPermissions($given, $required);
        $this->assertEquals($hasPerm, $ret);
    }

    public function permissionProvider(): array
    {
        return [
            [
                ["{all}:{all}:{all}"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["{all}:{all}:1"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["{all}:read:{all}"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["{all}:create:{all}"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["{all}:update:{all}"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["{all}:delete:{all}"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["user:delete:{all}"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["user:create:1", "user:delete:1"],
                ["{all}:{all}:{all}"],
                true
            ],
            [
                ["{all}:{all}:1"],
                ["{all}:{all}:1"],
                true
            ],
            [
                ["{all}:create:1"],
                ["{all}:create:1"],
                true
            ],
            [
                ["user:{all}:1"],
                ["{all}:create:1", "{all}:delete:1", "{all}:update:1", "{all}:read:1"],
                true
            ],
            [
                ["user:create:1"],
                ["user:create:1"],
                true
            ],
            [
                ["user:create:3"],
                ["user:create:1"],
                false
            ],
            [
                ["user:{all}:3"],
                ["user:create:1"],
                false
            ],
            [
                ["user:create:{all}"],
                ["user:create:1"],
                false
            ],
            [
                ["user:create:1"],
                ["user:read:1"],
                false
            ]
        ];
    }
}
