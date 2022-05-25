<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Integration\UserController;

use Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\ValidationExceptions\ArrayIsEmptyException;
use Exceptions\ValidationExceptions\InvalidPropertyException;

/**
 * Integration Tests for the updateUser method of UserController
 */
final class UciUpdateTest extends BaseUCITest
{
    /**
     * Tests if update throws exception on various situations
     * 
     * @dataProvider invalidUpdateFieldArrayProvider
     */
    public function testUpdateUserFails(int $id, array $properties, string $exception): void
    {
        $this->createUser();

        $this->expectException($exception);

        $this->userController->updateUser($id, $properties);
    }

    /**
     * Tests if update throws exception on various situations
     */
    public function testUpdateUser(): void
    {
        $this->createUser();

        $this->userController->updateUser(1, [
            "name"      => "myNewName",
            "postcode"  => "11111",
            "city"      => "yourCity",
            "phone"     => "111111111",
            "role"      => "admin"
        ]);
        //suppress risky warning
        $this->assertTrue(true);
    }


    public function invalidUpdateFieldArrayProvider(): array
    {
        return [
            "invalid userID" => [
                10, ["name" => "newName"], UserNotFoundException::class
            ],
            "empty array" => [
                1, [], ArrayIsEmptyException::class
            ],
            "unsupported property" => [
                1, ["quatsch" => "quatsch"], InvalidPropertyException::class
            ],
            "invalid type" => [
                1, ["name" => 123], InvalidPropertyException::class
            ],
            "invalid property" => [
                1, ["name" => "1!"], InvalidPropertyException::class
            ],
            "invalid role" => [
                1, ["role" => "quatsch"], RoleNotFoundException::class
            ],
        ];
    }
}
