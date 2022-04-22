<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\ECRNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;

/**
 * Testsuit for UserController->requestUsersEmailChange method
 */
final class UCRequestEmailChangeTest extends BaseUCTest
{

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testRequestEmailUserNotExists(): void
    {
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(true);

        $this->ecrAccessorMock->expects($this->once())
            ->method("insert")
            ->with($this->equalTo(1, "email", "code"))
            ->will($this->throwException(new UserNotFoundException()));


        $this->expectException(UserNotFoundException::class);
        $this->userController->requestUsersEmailChange(1, "");
    }

    /**
     * Tests if the method throws an exception if the Email is invalid
     */
    public function testRequestEmailWithInvalidEmail(): void
    {
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(["email" => "TO_SHORT"]);

        $this->expectException(InvalidFieldException::class);
        $this->expectExceptionMessage("TO_SHORT");

        $this->userController->requestUsersEmailChange(1, "email");
    }


    /**
     * Tests if the method throws an exception if the Email is not free
     * 
     * @dataProvider \BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller\Provider::NANDProvider()
     */
    public function testRequestEmailWithNotFreeEmail($emailFreeInUser, $emailFreeInEcr): void
    {
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "someEmail"]))
            ->willReturn(true);

        $this->configEmailAvailability($emailFreeInUser, $emailFreeInEcr);


        $this->expectException(DuplicateEmailException::class);
        $this->expectExceptionMessage("someEmail");

        $this->userController->requestUsersEmailChange(1, "someEmail");
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testRequestEmailSuccessful(): void
    {
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "email"]))
            ->willReturn(true);

        $this->configEmailAvailability(true, true);

        $this->ecrAccessorMock->expects($this->once())
            ->method("deleteByUserID")
            ->with($this->equalTo(1))
            ->will($this->throwException(new ECRNotFoundException()));

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
