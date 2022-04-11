<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use InvalidArgumentException;

class Validator implements ValidatorInterface
{
    /** 
     * Dictionary: Attribute => Validation method
     * 
     * its for choosing the right validator for each attribute.
     *
     * @var  array<string,array<string,string|int> $validationDict
     *  $validationDict = [
     *      attributeName => [
     *          func => (string) Name of validation function.
     *          errorCode => (int) Error code thrown if attribute is not valid.
     *      ]
     *  ]
     */
    private array $validationDict = [
        "email"     => ["func" => "isEmail",       "errorCode" => 100],
        "name"      => ["func" => "isWords",       "errorCode" => 101],
        "postcode"  => ["func" => "isPostcode",    "errorCode" => 102],
        "city"      => ["func" => "isWords",       "errorCode" => 103],
        "phone"     => ["func" => "isPhoneNumber", "errorCode" => 104],
        "password"  => ["func" => "isPassword",    "errorCode" => 105]
    ];

    public function validate(array $attr): void
    {

        //checks if all attributes can be validated
        //throws Exception if not
        foreach ($attr as $key => $value) {
            if (!array_key_exists($key, $this->validationDict)) {
                throw new InvalidArgumentException("Attribute " . $key . " cant be validated");
            }
        }

        //validate each attribute by the right validation method
        foreach ($attr as $key => $value) {
            //get validation method an errorCode
            $validFunc = $this->validationDict[$key]["func"];
            $errCode = $this->validationDict[$key]["errorCode"];

            //throw Exception if not valid
            if (!$this->{$validFunc}($value)) {
                throw new InvalidAttributeException($value . " is not a valid " . $key, $errCode);
            }
        }
    }

    /**
     * Validates if a string is a valid email address
     *
     * @param  string $email    The string to validate.
     * @return bool Returns true if it is a valid email address, else otherwise.
     */
    private function isEmail(string $email): bool
    {
        //email's longer than 100 characters are not allowed
        if (strlen($email) > 100) return false;

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    //TODO add a config 
    /**
     * Validates if a string is a valid password
     *
     * @param  string $email    The string to validate.
     * @return bool Returns true if it is a valid password, else otherwise.
     */
    private function isPassword(string $pass): bool
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

    /**
     * Validates if a string are valid words separated by spaces
     * 
     * @param  string $email    The string to validate.
     * @return bool Returns true if its valid, else otherwise.
     */
    private function isWords(string $words): bool
    {
        //regex for a "word" (only letters and at least 2 characters)
        $word = "[a-zA-ZÄÖÜäöüß]{2,}";

        //one or more words with spaces between
        if (preg_match("/^(" . $word . "[ ])*" . $word . "$/", $words) !== 1) return false;

        return true;
    }

    /**
     * Validates if a string is a valid postcode
     *
     * @param  string $email    The string to validate.
     * @return bool Returns true if it is a valid postcode, else otherwise.
     */
    private function isPostcode(string $postcode): bool
    {
        //postcode need exact 5 characters
        if (strlen($postcode) !== 5) return false;

        //postcode only contains numbers
        if (preg_match("/^[0-9]+$/", $postcode) !== 1) return false;

        return true;
    }

    /**
     * Validates if a string is a valid phone number
     *
     * @param  string $email    The string to validate.
     * @return bool Returns true if it is a valid phone number, else otherwise.
     */
    private function isPhoneNumber(string $phone): bool
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
