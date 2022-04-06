<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces;


// interface to interact with the user data resource
interface UserAccessorInterface
{
    //insert a new user
    public static function insert(string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id): void;

    //delete an existing user by userID
    public static function delete(int $id): void;

    //update an existing user
    public static function update(int $id, string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id): void;

    //find user for specified email and return userID or null if there is no user with this email
    public static function findByEmail(string $email): ?int;
}
