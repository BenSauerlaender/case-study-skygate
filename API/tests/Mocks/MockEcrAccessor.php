<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Mocks;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\EcrAccessorInterface;

// functions to interact with the emailChangeRequest-db-table
class MockEcrAccessor implements EcrAccessorInterface
{
    //find Request by userID and return id, newEmail and verificationCode
    public static function findByUserID(int $user_id): ?array
    {
        return null;
    }

    //find Request for specified email and return id or null if there is no user with this email
    public static function findByEmail(string $email): ?int
    {
        if ($email === "duplicate") return 0;
        else return null;
    }

    //delete request by ID
    public static function delete(int $id): void
    {
    }

    //delete request by userID
    public static function deleteByUserID(int $user_id): void
    {
    }

    //insert a new request
    public static function insert(int $user_id, string $new_email, string $verification_code): void
    {
    }
}
