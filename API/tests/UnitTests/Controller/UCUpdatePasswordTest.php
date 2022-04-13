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
 * Testsuit for UserController->updateUsersPassword method
 */
final class UCUpdatePasswordTest extends BaseUCTest
{

    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testUpdatePasswordWithIDOutOfRange(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("is not a valid id");

        $this->userController->updateUsersPassword(-1, "", "");
    }

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testUpdatePasswordUserNotExists(): void
    {
        // userAccessor-> get will return always null.
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no user with id");
        $this->userController->updateUsersPassword(1, "", "");
    }

    /**
     * Tests if the method throws an exception if the old password is incorrect
     */
    public function testUpdatePasswordWithIncorrectPass(): void
    {
        // userAccessor-> get will return a fake hashed password
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["hashedPass" => "hash"]);

        //all passwords will be correct
        $this->securityUtilitiesMock->expects($this->once())
            ->method("checkPassword")
            ->willReturn(true);

        //validation will fail
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]))
            ->will($this->throwException(new InvalidAttributeException()));

        $this->expectException(InvalidAttributeException::class);
        $this->userController->updateUsersPassword(1, "newPass", "oldPass");
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testUpdatePasswordSuccessful(): void
    {
        // userAccessor-> get will return a fake hashed password
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["hashedPass" => "hash"]);

        //all passwords will be correct
        $this->securityUtilitiesMock->expects($this->once())
            ->method("checkPassword")
            ->with($this->equalTo("oldPass", "hash"))
            ->willReturn(true);

        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]));

        $this->securityUtilitiesMock->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo("newPass"))
            ->willReturn("newHash");

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["hashedPass" => "newHash"]));

        $this->userController->updateUsersPassword(1, "newPass", "oldPass");
    }
}
