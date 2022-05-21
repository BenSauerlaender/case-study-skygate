<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller\UserController;

use BenSauer\CaseStudySkygateApi\Objects\Responses\BadRequestResponses\UserNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;

use function PHPSTORM_META\map;

/**
 * Testsuit for UserController->getUser method
 */
final class UCGetTest extends BaseUCTest
{
    /**
     * Tests if the method throws an Exception if the requested user not exists
     */
    public function testGetAUserThatNotExists(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with(1)
            ->will($this->throwException(new UserNotFoundException()));

        $this->userController->getUser(1);
    }

    /**
     * Tests if the return the correct array if the user exists
     */
    public function testGetUserSuccessful(): void
    {
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with(1)
            ->willReturn([
                "user_id" => 1,
                "email" => "user4@mail.de",
                "name" => "user4",
                "postcode" => "00000",
                "city" => "city",
                "phone" => "0123",
                "hashed_pass" => "1",
                "verified" => 1,
                "verification_code" => null,
                "roleID" => 3
            ]);

        $this->roleAccessorMock->expects($this->once())
            ->method("get")
            ->with(3)
            ->willReturn([
                "id" => 3,
                "name" => "user",
                "permissions" => "",
            ]);

        $ret = $this->userController->getUser(1);
        $this->assertEquals([
            "email" => "user4@mail.de",
            "name" => "user4",
            "postcode" => "00000",
            "city" => "city",
            "phone" => "0123",
            "role" => "user"
        ], $ret);
    }
}
