<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Mocks;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\UserAccessorInterface;

class MockUserAccessor implements UserAccessorInterface
{
    public static array $inserted;

    public static function insert(string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id): void
    {
        self::$inserted = array(
            "email" => $email,
            "name" => $name,
            "postcode" => $postcode,
            "city" => $city,
            "phone" => $phone,
            "hashedpass" => $hashed_pass,
            "verified" => $verified,
            "verificationCode" => $verificationCode,
            "roleID" => $role_id
        );
    }

    public static function delete(int $id): void
    {
    }

    public static function update(int $id, string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id): void
    {
    }

    public static function findByEmail(string $email): ?int
    {
        if ($email === "true") return 0;
        else return null;
    }
}
