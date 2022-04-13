<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for UserController->updateUsersPassword method
 */
final class UCUpdatePasswordTest extends TestCase
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testUpdatePasswordWithIDOutOfRange(): void
    {

        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );


        $this->expectException(OutOfRangeException::class);

        $uc->updateUsersPassword(-1, "", "");
    }

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testUpdatePasswordUserNotExists(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        // userAccessor-> get will return always null.
        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(null);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidArgumentException::class);
        $uc->updateUsersPassword(1, "", "");
    }

    /**
     * Tests if the method throws an exception if the old password is incorrect
     */
    public function testUpdatePasswordWithIncorrectPass(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        // userAccessor-> get will return a fake hashed password
        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["hashedPass" => "hash"]);

        //all passwords will be correct
        $secUtil->expects($this->once())
            ->method("checkPassword")
            ->willReturn(true);

        //validation will fail
        $validator->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]))
            ->will($this->throwException(new InvalidAttributeException));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidAttributeException::class);
        $uc->updateUsersPassword(1, "newPass", "oldPass");
    }


    /**
     * Tests if the method calls all functions correct
     */
    public function testUpdatePasswordSuccessful(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        // userAccessor-> get will return a fake hashed password
        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["hashedPass" => "hash"]);

        //all passwords will be correct
        $secUtil->expects($this->once())
            ->method("checkPassword")
            ->with($this->equalTo("oldPass", "hash"))
            ->willReturn(true);

        $validator->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]));

        $secUtil->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo("newPass"))
            ->willReturn("newHash");

        $userAcc->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["hashedPass" => "newHash"]));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $uc->updateUsersPassword(1, "newPass", "oldPass");
    }
}
