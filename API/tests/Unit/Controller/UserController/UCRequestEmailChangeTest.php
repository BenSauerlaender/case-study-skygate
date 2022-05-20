<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;
use Dotenv\Exception\InvalidFileException;

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
        $this->ValidationControllerMock->expects($this->once())
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
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->willReturn(["email" => ["TO_SHORT"]]);

        $this->expectException(InvalidPropertyException::class);

        $this->userController->requestUsersEmailChange(1, "email");
    }


    /**
     * Tests if the method throws an exception if the Email is not free
     * 
     * @dataProvider \BenSauer\CaseStudySkygateApi\tests\Unit\Controller\UserController\Provider::NANDProvider()
     */
    public function testRequestEmailWithNotFreeEmail($emailFreeInUser, $emailFreeInEcr): void
    {
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "someEmail"]))
            ->willReturn(true);

        $this->configEmailAvailability($emailFreeInUser, $emailFreeInEcr);


        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage("Invalid fields with Reasons: email: IS_TAKEN");

        $this->userController->requestUsersEmailChange(1, "someEmail");
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testRequestEmailSuccessful(): void
    {
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "email"]))
            ->willReturn(true);

        $this->configEmailAvailability(true, true);

        $this->ecrAccessorMock->expects($this->once())
            ->method("deleteByUserID")
            ->with($this->equalTo(1))
            ->will($this->throwException(new EcrNotFoundException()));

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
