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
 * Testsuit for UserController->update method
 */
final class UCUpdateTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testUpdateUserIDOutOfRange(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("is not a valid id");

        $this->userController->updateUser(-1, []);
    }

    /**
     * Tests if the method throws an exception if th attribute array is empty
     */
    public function testUpdateUserWithoutArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The attribute array is empty");

        $this->userController->updateUser(1, []);
    }

    /**
     * Tests if the method throws an Exception if at least one of the arguments is not supposed to update with this method
     *
     * @dataProvider invalidArgumentProvider
     */
    public function testUpdateUserWithInvalidArgument(array $input, string $exceptionMessage): void
    {
        //validator will throw InvalidArgumentException every time.
        //validator will only be called on "quatsch". "password" and "email"will be catched before
        $this->validatorMock
            ->method("validate")
            ->with($this->equalTo(["quatsch" => ""]))
            ->will($this->throwException(new InvalidArgumentException));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->userController->updateUser(1, $input);
    }

    /**
     * Provides Arguments that cant be updated.
     */
    public function invalidArgumentProvider(): array
    {
        return [
            [["password" => ""], "To change the password"],
            [["email" => ""], "To change the email"],
            [["quatsch" => ""], ""]
        ];
    }

    /**
     * Tests if the method throws an Exception if at least one of the arguments is invalid
     *
     * @dataProvider invalidAttributeProvider
     */
    public function testUpdateUserWithInvalidAttribute(string $key, string $value): void
    {
        $this->validatorMock->method("validate")
            ->will($this->throwException(new InvalidAttributeException));

        $this->roleAccessorMock->method("findByName")
            ->willReturn(null);

        $this->expectException(InvalidAttributeException::class);

        $this->userController->updateUser(1, [$key => $value]);
    }

    /**
     * Provides Arguments that cant be updated.
     */
    public function invalidAttributeProvider(): array
    {
        return [
            ["name", "noName"],
            ["role", "noRole"]
        ];
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     *
     * @dataProvider successProvider
     */
    public function testUpdateUserSuccessful(array $input, array $validate, array $update): void
    {
        $userID = 1;

        //validator will validate everything
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($validate));

        if (array_key_exists("role", $input)) {
            //will find always the roleID = 0
            $this->roleAccessorMock->expects($this->once())
                ->method("findByName")
                ->with($this->equalTo($input["role"]))
                ->willReturn(0);
        }

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->with($this->equalTo($userID, $update));

        $this->userController->updateUser($userID, $input);
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
