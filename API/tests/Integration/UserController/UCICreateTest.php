<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidTypeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\UnsupportedFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ValidationException;

/**
 * Integration Tests for the createUser method of UserController
 */
final class UCICreateTest extends BaseUCITest
{
    /**
     * Tests if the user creation throws Validation Exception by an invalid field-array
     * 
     * @dataProvider invalidFieldArrayProvider
     */
    public function testCreateUserFailsOnInvalidFieldData(array $fields, string $exception, string $msg): void
    {
        $this->expectException(ValidationException::class);
        $this->expectException($exception);
        $this->expectExceptionMessage($msg);

        $this->userController->createUser($fields);
    }

    /**
     * Tests if the user creation succeeds
     * 
     * @dataProvider fieldArrayProvider
     */
    public function testCreateUser(array $fields): void
    {
        $response = $this->userController->createUser($fields);
        $this->assertArrayHasKey("id", $response);
        $this->assertIsInt($response["id"]);
        $this->assertEquals(1, $response["id"]);

        $this->assertArrayHasKey("verificationCode", $response);
        $this->assertIsString($response["verificationCode"]);
        $this->assertEquals(10, strlen($response["verificationCode"]));
    }

    /**
     * Tests if the creation fails if the email is already taken
     */
    public function testCreateUserFailsOnSameEmail(): void
    {
        $this->createUser();

        $this->expectException(DuplicateEmailException::class);

        $this->userController->createUser(
            [
                "email"     => "myEmail@mail.de",
                "name"      => "myName",
                "postcode"  => "12345",
                "city"      => "myCity",
                "phone"     => "123456789",
                "password"  => "MyPassword1"
            ]
        );
    }

    public function fieldArrayProvider(): array
    {
        return [
            "with role" => [
                [
                    "email"     => "myEmail@mail.de",
                    "name"      => "myName",
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "MyPassword1",
                    "role"  => "user"
                ]
            ],
            "without role" => [
                [
                    "email"     => "myEmail@mail.de",
                    "name"      => "myName",
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "MyPassword1",
                ]
            ]
        ];
    }

    public function invalidFieldArrayProvider(): array
    {
        return [
            "missing email and name" => [
                [
                    "postcode"  => "myPostcode",
                    "city"      => "myCity",
                    "phone"     => "myPhone",
                    "password"  => "MyPassword",
                    "role"      => "myRole"
                ], RequiredFieldException::class, "Missing fields: email,name"
            ],
            "unsupported field" => [
                [
                    "quatsch"     => "",
                    "email"     => "myEmail",
                    "name"      => "myName",
                    "postcode"  => "myPostcode",
                    "city"      => "myCity",
                    "phone"     => "myPhone",
                    "password"  => "MyPassword",
                    "role"      => "myRole"
                ], UnsupportedFieldException::class, "Field: quatsch"
            ],
            "invalid type" => [
                [
                    "email"     => "myEmail@mail.de",
                    "name"      => 123,
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "MyPassword1",
                    "role"      => "myRole"
                ], InvalidTypeException::class, "name"
            ],
            "invalid email and password" => [
                [
                    "email"     => "myEmail",
                    "name"      => "myName",
                    "postcode"  => "12345",
                    "city"      => "myCity",
                    "phone"     => "123456789",
                    "password"  => "mypassword",
                ], InvalidFieldException::class, "Invalid fields with reasons: email=NO_EMAIL,password=NO_UPPER_CASE+NO_NUMBER"
            ]
        ];
    }
}
