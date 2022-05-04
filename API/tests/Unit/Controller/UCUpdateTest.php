<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ArrayIsEmptyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidTypeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\UnsupportedFieldException;

/**
 * Testsuit for UserController->update method
 */
final class UCUpdateTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if there is no user with specified id
     */
    public function testUpdateWithNonExistingUser(): void
    {
        //ValidationController will validate everything
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->willReturn(true);

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->with($this->equalTo(-1, ["name" => "name"]))
            ->will($this->throwException(new UserNotFoundException()));

        $this->expectException(UserNotFoundException::class);

        $this->userController->updateUser(-1, ["name" => "name"]);
    }

    /**
     * Tests if the method throws an exception if th field array is empty
     */
    public function testUpdateUserWithoutFields(): void
    {
        $this->expectException(ArrayIsEmptyException::class);

        $this->userController->updateUser(1, []);
    }

    /**
     * Tests if the method throws an Exception if at least one of the arguments is not supposed to update with this method
     *
     * @dataProvider invalidArgumentProvider
     */
    public function testUpdateUserWithUnsupportedField(string $field): void
    {
        //ValidationController will throw InvalidArgumentException every time.
        //ValidationController will only be called on "quatsch". "password" and "email"will be catched before
        $this->ValidationControllerMock
            ->method("validate")
            ->with($this->equalTo(["quatsch" => ""]))
            ->will($this->throwException(new UnsupportedFieldException()));

        $this->expectException(UnsupportedFieldException::class);

        $this->userController->updateUser(1, [$field => ""]);
    }

    /**
     * Provides Arguments that cant be updated.
     */
    public function invalidArgumentProvider(): array
    {
        return [
            ["password"],
            ["email"],
            ["quatsch"]
        ];
    }

    /**
     * Tests if the method throws an Exception if at least one of the fields is invalid
     *
     * @dataProvider invalidFieldProvider
     */
    public function testUpdateUserWithInvalidField(string $key, string $value): void
    {
        $this->ValidationControllerMock->method("validate")
            ->willReturn([$key => "INVALID"]);

        $this->roleAccessorMock->method("findByName")
            ->willReturn(null);

        $this->expectException(InvalidFieldException::class);
        $this->expectExceptionMessage($key);

        $this->userController->updateUser(1, [$key => $value]);
    }

    /**
     * Tests if the method throws an Exception if at least one of the fields has a wrong type
     */
    public function testUpdateUserWithInvalidFieldType(): void
    {
        $this->ValidationControllerMock->method("validate")
            ->will($this->throwException(new InvalidTypeException()));

        $this->expectException(InvalidTypeException::class);

        $this->userController->updateUser(1, ["name" => 123]);
    }

    /**
     * Provides Arguments that cant be updated.
     */
    public function invalidFieldProvider(): array
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

        //ValidationController will validate everything
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($validate))
            ->willReturn(true);

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
