<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\UserAccessorInterface;

// interface to interact with the user-db-table
class MySqlUserAccessor implements UserAccessorInterface
{

    //insert a new user
    public static function insert(string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id): void
    {
    }

    //delete an existing user by userID
    public static function delete(int $id): void
    {
    }

    //update an existing user
    public static function update(int $id, string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id): void
    {
    }

    //find User for specified email and return user_id or null if there is no user with this email
    public static function findByEmail(string $email): ?int
    {
    }
}
