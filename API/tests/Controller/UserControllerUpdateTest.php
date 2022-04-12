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
 * Testsuit for UserController->update method
 */
final class UserControllerUpdateTest extends TestCase
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testUpdateUserIDOutOfRange(): void
    {
        $id = -1;

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

        $uc->updateUser(-1, []);
    }


    /**
     * Tests if the method throws an Exception if at least one of the arguments is not supposed to update with this method
     *
     * @dataProvider invalidArgumentProvider
     */
    public function testUpdateUserWithInvalidArgument(array $input): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);


        //validator will throw InvalidArgumentException every time.
        //validator will only be called on "quatsch", "password" will be catched before
        $validator
            ->method("validate")
            ->with($this->equalTo(["quatsch" => ""]))
            ->will($this->throwException(new InvalidArgumentException));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidArgumentException::class);

        $uc->updateUser(1, $input);
    }

    /**
     * Provides Arguments that cant be updated.
     */
    public function invalidArgumentProvider(): array
    {
        return [
            [["password" => ""]],
            [["quatsch" => ""]],
            [[]]
        ];
    }

    /**
     * Tests if the method throws an Exception if at least one of the arguments is invalid
     *
     * @dataProvider invalidAttributeProvider
     */
    public function testUpdateUserWithInvalidAttribute(string $key, string $value): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);


        //validator will throw InvalidAttributeException every time.
        $validator->method("validate")
            ->will($this->throwException(new InvalidAttributeException));

        $roleAcc
            ->method("findByName")
            ->willReturn(null);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidAttributeException::class);

        $uc->updateUser(1, [$key => $value]);
    }

    /**
     * Provides Arguments that cant be updated.
     */
    public function invalidAttributeProvider(): array
    {
        return [
            ["email", "noEmail"],
            ["role", "noRole"]
        ];
    }

    /**
     * Tests if the method calls the right functions
     *
     * @dataProvider successProvider
     */
    public function testUpdateUserSuccessful(array $input, array $validate, array $update): void
    {
        $userID = 1;

        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);


        //validator will validate everything
        $validator->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($validate));

        if (array_key_exists("role", $input)) {
            //will find always a the roleID = 0
            $roleAcc->expects($this->once())
                ->method("findByName")
                ->with($this->equalTo($input["role"]))
                ->willReturn(0);
        }

        $userAcc->expects($this->once())
            ->method("update")
            ->with($this->equalTo($userID, $update));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $uc->updateUser($userID, $input);
    }

    /**
     * Provides Arguments that lead to success
     */
    public function successProvider(): array
    {
        return [
            "change Email" => [
                ["name" => "newName"],
                ["name" => "newName"],
                ["name" => "newName"]
            ],
            "change role" => [
                ["role" => "newRole"],
                [],
                ["roleID" => 0]
            ],
            "change all" => [
                [
                    "name" => "newName",
                    "postcode" => "newPostcode",
                    "city" => "newCity",
                    "phone" => "newPhone",
                    "role" => "newRole"
                ],
                [
                    "name" => "newName",
                    "postcode" => "newPostcode",
                    "city" => "newCity",
                    "phone" => "newPhone"
                ],
                [
                    "name" => "newName",
                    "postcode" => "newPostcode",
                    "city" => "newCity",
                    "phone" => "newPhone",
                    "roleID" => 0
                ]
            ]
        ];
    }
}
