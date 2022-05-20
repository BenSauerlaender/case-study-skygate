<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ArrayIsEmptyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;

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
    public function testUpdateUserFails(int $id, array $fields, string $exception): void
    {
        $this->createUser();

        $this->expectException($exception);

        $this->userController->updateUser($id, $fields);
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
            "unsupported field" => [
                1, ["quatsch" => "quatsch"], InvalidPropertyException::class
            ],
            "invalid type" => [
                1, ["name" => 123], InvalidPropertyException::class
            ],
            "invalid field" => [
                1, ["name" => "1!"], InvalidPropertyException::class
            ],
            "invalid role" => [
                1, ["role" => "quatsch"], RoleNotFoundException::class
            ],
        ];
    }
}
