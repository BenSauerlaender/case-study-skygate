<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Testsuit for UserController->requestUsersEmailChange method
 */
final class UCRequestEmailChangeTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testRequestEmailWithIDOutOfRange(): void
    {

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("is not a valid id");

        $this->userController->requestUsersEmailChange(-1, "");
    }

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testRequestEmailUserNotExists(): void
    {

        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no user with id");
        $this->userController->requestUsersEmailChange(1, "");
    }

    /**
     * Tests if the method throws an exception if the Email is invalid
     */
    public function testRequestEmailWithInvalidEmail(): void
    {

        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn([]);

        $this->validatorMock->method("validate")
            ->will($this->throwException(new InvalidAttributeException));

        $this->expectException(InvalidAttributeException::class);
        $this->userController->requestUsersEmailChange(1, "email");
    }


    /**
     * Tests if the method throws an exception if the Email is not free
     * 
     * @dataProvider \BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller\Provider::NANDProvider()
     */
    public function testRequestEmailWithNotFreeEmail($emailFreeInUser, $emailFreeInEcr): void
    {
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn([]);

        $this->configEmailAvailability($emailFreeInUser, $emailFreeInEcr);


        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionCode(110);
        $this->expectExceptionMessage("is already in use");

        $this->userController->requestUsersEmailChange(1, "email");
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testRequestEmailSuccessful(): void
    {

        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn([]);

        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "email"]));

        $this->configEmailAvailability(true, true);

        $this->ecrAccessorMock->expects($this->once())
            ->method("deleteByUserID")
            ->with($this->equalTo(1));

        $this->securityUtilitiesMock->expects($this->once())
            ->method("generateCode")
            ->with($this->equalTo(10))
            ->willReturn("code");

        $this->ecrAccessorMock->expects($this->once())
            ->method("insert")
            ->with($this->equalTo(1, "email", "code"));


        $code = $this->userController->requestUsersEmailChange(1, "email");
        $this->assertEquals("code", $code);
    }
}
