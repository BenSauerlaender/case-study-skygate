<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities\Interfaces;

use BenSauer\CaseStudySkygateApi\Models\Interfaces\UserInterface;

interface UserUtilitiesInterface
{
    /* Validates all properties 
     * Creates new user and inserts into data source 
     *
     * invalidArgumentException codes from 100 to 106
    */
    public static function createNewUser(string $email, string $name, string $postcode, string $city, string $phone, string $role, string $password): UserInterface;

    /*
     * insert a new request to the data source
     * returns the verification code
    */
    public static function createEmailChangeRequest(int $userID, string $newEmail): string;

    //checks if verification code is correct than deletes the request and return the new email
    public static function verifyEmailChangeRequest(int $userID, string $verificationCode): string;

    //hash the specified password
    public static function hashPassword(string $pass): string;

    //returns true if password is correct; otherwise: false
    public static function checkPassword(string $pass, string $hashedPassword): bool;
}
