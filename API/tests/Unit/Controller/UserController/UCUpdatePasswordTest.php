<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;
use BenSauer\CaseStudySkygateApi\tests\Unit\tests\helper\TableCreator\UserTableTest;

/**
 * Testsuit for UserController->updateUsersPassword method
 */
final class UCUpdatePasswordTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testUpdatePasswordUserNotExists(): void
    {
        // userAccessor-> get will return always null.
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->will($this->throwException(new UserNotFoundException()));

        $this->expectException(UserNotFoundException::class);
        $this->userController->updateUsersPassword(1, "", "");
    }

    /**
     * Tests if the method throws an exception if old Password is incorrect
     */
    public function testUpdatePasswordWithIncorrectPass(): void
    {
        // userAccessor-> get will return a fake hashed password
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["hashedPass" => "hash"]);

        //all passwords will be incorrect
        $this->SecurityControllerMock->expects($this->once())
            ->method("checkPassword")
            ->willReturn(false);

        $return = $this->userController->updateUsersPassword(1, "newPass", "oldPass");
        $this->assertEquals(false, $return);
    }

    /**
     * Tests if the method throws an exception if new Password is invalid
     */
    public function testUpdatePasswordWithInvalidPass(): void
    {
        // userAccessor-> get will return a fake hashed password
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["hashedPass" => "hash"]);

        //all passwords will be correct
        $this->SecurityControllerMock->expects($this->once())
            ->method("checkPassword")
            ->willReturn(true);

        //validation will fail
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]))
            ->willReturn(["password" => ["TO_SHORT"]]);

        $this->expectException(InvalidPropertyException::class);
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
        $this->SecurityControllerMock->expects($this->once())
            ->method("checkPassword")
            ->with($this->equalTo("oldPass", "hash"))
            ->willReturn(true);

        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]))
            ->willReturn(true);

        $this->SecurityControllerMock->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo("newPass"))
            ->willReturn("newHash");

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["hashedPass" => "newHash"]));

        $return = $this->userController->updateUsersPassword(1, "newPass", "oldPass");
        $this->assertEquals(true, $return);
    }
}
