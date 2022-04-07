<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;

// class with several static functions to validate data
class Validator implements ValidatorInterface
{

    public static function isEmail(string $email): bool
    {
        //email's longer than 100 characters are not allowed
        if (strlen($email) > 100) return false;

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    //TODO add a config 
    public static function isPassword(string $pass): bool
    {
        //password is at least 8 characters long
        if (strlen($pass) < 8) return false;

        //password is not longer than 50 characters
        if (strlen($pass) > 50) return false;

        //password only contains letters(also umlaute), numbers and these special characters: # ? ! @ $ % ^ & . * - +
        if (preg_match("/^[a-zA-ZÄÖÜäöüß0-9#?!@$%^&.*\-+]*$/", $pass) !== 1) return false;

        //password contains at least one lower case letter
        if (preg_match("/[a-zäöüß]+/", $pass) !== 1) return false;

        //password contains at least one upper case letter
        if (preg_match("/[A-ZÄÖÜ}]+/", $pass) !== 1) return false;

        //password contains at least one number
        if (preg_match("/[0-9]+/", $pass) !== 1) return false;

        return true;
    }

    public static function isWords(string $words): bool
    {
        //regex for a "word" (only letters and at least 2 characters)
        $word = "[a-zA-ZÄÖÜäöüß]{2,}";

        //one or more words with spaces between
        if (preg_match("/^(" . $word . "[ ])*" . $word . "$/", $words) !== 1) return false;

        return true;
    }

    public static function isPostcode(string $postcode): bool
    {
        //postcode need exact 5 characters
        if (strlen($postcode) !== 5) return false;

        //postcode only contains numbers
        if (preg_match("/^[0-9]+$/", $postcode) !== 1) return false;

        return true;
    }

    public static function isPhoneNumber(string $phone): bool
    {
        $onlyNumbers = preg_replace("/[^0-9]/", "", $phone);

        //phonenumber is at least 10 characters long
        if (strlen($onlyNumbers) < 8) return false;

        //phonenumber is not longer than 20 characters
        if (strlen($onlyNumbers) > 15) return false;

        //phonenumber only contains numbers, spaces and + ( ) - / . x
        if (preg_match("/^[0-9 +\-()\/.x]*$/", $phone) !== 1) return false;

        return true;
    }
}
