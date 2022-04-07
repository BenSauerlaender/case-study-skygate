<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DatabaseException;
use BenSauer\CaseStudySkygateApi\Exceptions\FoundMoreThanOneElementException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidFunctionCallException;
use BenSauer\CaseStudySkygateApi\Exceptions\MissingDependencyException;
use BenSauer\CaseStudySkygateApi\Models\Interfaces\UserInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\UserSearchQueryInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\UserUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use Exception;

class UserUtilities implements UserUtilitiesInterface
{

    //instances of implementations of required interfaces
    private static ?EcrAccessorInterface $ecrAccessor = null;
    private static ?RoleAccessorInterface $roleAccessor = null;
    private static ?UserAccessorInterface $userAccessor = null;
    private static ?ValidatorInterface $validator = null;
    private static ?UserSearchQueryInterface $userQuery = null;

    //set up the class with implementations of interfaces, that are needed
    public static function setUp(EcrAccessorInterface $ecrAccessor, RoleAccessorInterface $roleAccessor, UserAccessorInterface $userAccessor, ValidatorInterface $validator, UserSearchQueryInterface $userQuery): void
    {
        self::$ecrAccessor = $ecrAccessor;
        self::$roleAccessor = $roleAccessor;
        self::$userAccessor = $userAccessor;
        self::$validator = $validator;
        self::$userQuery = $userQuery;
    }

    /* Validates all properties 
     * Creates new user and inserts into data source 
     *
     * invalidArgumentException codes from 100 to 106
    */
    public static function createNewUser(
        string $email,
        string $name,
        string $postcode,
        string $city,
        string $phone,
        string $role,
        string $password
    ): UserInterface {

        //check all dependencies
        if (is_null(self::$roleAccessor)) throw new MissingDependencyException("RoleAccessor is not set up");
        if (is_null(self::$userAccessor)) throw new MissingDependencyException("UserAccessor is not set up");
        if (is_null(self::$validator)) throw new MissingDependencyException("Validator is not set up");
        if (is_null(self::$userQuery)) throw new MissingDependencyException("UserSearchQuery is not set up");

        //validate all inputs
        if (!self::$validator->isEmail($email)) throw new \InvalidArgumentException("No valid email", 100);
        if (!self::$validator->isWords($name)) throw new \InvalidArgumentException("No valid name", 101);
        if (!self::$validator->isPostcode($postcode)) throw new \InvalidArgumentException("No valid postcode", 102);
        if (!self::$validator->isWords($city)) throw new \InvalidArgumentException("No valid city", 103);
        if (!self::$validator->isPhoneNumber($phone)) throw new \InvalidArgumentException("No valid phone", 104);
        if (!self::$validator->isPassword($password)) throw new \InvalidArgumentException("No valid password", 105);

        //check if email already exists 
        if (!self::isEmailFree($email)) throw new \InvalidArgumentException("Email already in use", 110);

        // search for role with the specified name
        $role_obj = self::$roleAccessor->findByName($role);

        // throw exception if there is no role with this name
        if (is_null($role_obj)) throw new \InvalidArgumentException("No valid role", 106);

        //get role id from role name
        $role_id = $role_obj->getID();

        //generate password hash
        $password_hash = self::hashPassword($password);

        //generate a 10 character code
        $verificationCode = self::generateCode(10);

        //insert new user to data source
        self::$userAccessor->insert($email, $name, $postcode, $city, $phone, $password_hash, false, $verificationCode, $role_id);

        try {
            //get new user from data source
            $newUser = (self::$userQuery->getNewInstance())->addFilter("email", $email)->getOne();
        } catch (FoundMoreThanOneElementException $e) {
            throw new DatabaseException("Try to get new user; email:" . $email, 0, $e);
        }

        if (is_null($newUser)) throw new DatabaseException("new user was inserted, but then not found in the dataBase. Email: " . $email);

        //return the new user
        return $newUser;
    }

    // checks if a specified email is free (not used or in an email change request)
    private static function isEmailFree(string $email): bool
    {
        if (!is_null((self::$userAccessor->findByEmail($email)))) return false;
        if (!is_null((self::$ecrAccessor->findByEmail($email)))) return false;
        return true;
    }

    //hash the specified password
    public static function hashPassword(string $pass): string
    {
        return password_hash($pass, PASSWORD_BCRYPT);
    }

    //returns true if password is correct; otherwise: false
    public static function checkPassword(string $pass, string $hashedPassword): bool
    {
        return password_verify($pass, $hashedPassword);
    }

    //generate a semi-random hex-string with a given length
    private static function generateCode(int $length): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /*
     * insert a new request to the data source
     * returns the verification code
    */
    public static function createEmailChangeRequest(int $userID, string $newEmail): string
    {
        //check if email already exists 
        if (!self::isEmailFree($newEmail)) throw new \InvalidArgumentException("Email already in use", 110);

        //delete old requests
        self::$ecrAccessor->deleteByUserID($userID);

        //generate a 10 character length hex-string 
        $verificationCode = bin2hex(random_bytes(5));

        //save the request in the DB
        self::$ecrAccessor->insert($userID, $newEmail, $verificationCode);

        return $verificationCode;
    }

    //checks if verification code is correct than deletes the request and return the new email
    public static function verifyEmailChangeRequest(int $userID, string $verificationCode): string
    {
        //get the request
        $request = self::$ecrAccessor->findByUserID($userID);

        //throw exception if there is no request
        if (is_null($request)) throw new InvalidFunctionCallException("There is no EmailChangeRequest for user_id:" . $userID);

        //check if the code is right
        if ($request["verificationCode"] !== $verificationCode) throw new \InvalidArgumentException("Invalid verification code");

        //remove request
        self::$ecrAccessor->delete($request["id"]);

        return $request["newEmail"];
    }
}
