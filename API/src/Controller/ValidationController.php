<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidTypeException;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ValidationControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\UnsupportedFieldException;
use TypeError;

class ValidationController implements ValidationControllerInterface
{
    /** 
     * Dictionary: field => validation method
     * 
     * its for choosing the right Validator for each field.
     *
     * @var  array<string,string> $getValidator = [fieldName => validationFunction]
     */
    private array $getValidator = [
        "email"     => "isEmail",
        "name"      => "isWords",
        "postcode"  => "isPostcode",
        "city"      => "isWords",
        "phone"     => "isPhoneNumber",
        "password"  => "isPassword"
    ];

    public function validate(array $fields): mixed
    {
        //checks if all fields are supported
        //throws Exception if not
        foreach ($fields as $key => $value) {
            if (!array_key_exists($key, $this->getValidator)) {
                throw new UnsupportedFieldException("Field: $key");
            }
        }

        $invalidFields = [];

        //validate each field by the right validation method
        foreach ($fields as $key => $value) {
            //get validation method and errorCode
            $Validator = $this->getValidator[$key];

            //if is not valid: collect reasons
            try {
                $return = $this->{$Validator}($value);
                if ($return !== true) {
                    $invalidFields += [$key => implode("+", $return)];
                }
            } catch (TypeError $e) {
                throw new InvalidTypeException("Type of $key is not valid", 0, $e);
            }
        }

        //return true if every field is valid
        if (sizeof($invalidFields) === 0) return true;

        //otherwise return the reasons
        return $invalidFields;
    }

    /**
     * Validates if a string is a valid email address
     *
     * @param  string $email        The string to validate.
     * @return true|array<string>   returns true if it is a valid email address, otherwise an array with reasons its not.
     */
    private function isEmail(string $email): mixed
    {
        $reasons = [];

        //email's longer than 100 characters are not allowed
        if (strlen($email) > 100) $reasons += ["TO_LONG"];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $reasons += ["NO_EMAIL"];

        if (sizeof($reasons) === 0) return true;
        else return $reasons;
    }

    //TODO add a config 
    /**
     * Validates if a string is a valid password
     *
     * @param  string $password     The string to validate.
     * @return true|array<string>   returns true if it is a valid password, otherwise an array with reasons its not.
     */
    private function isPassword(string $pass): mixed
    {
        $reasons = [];

        //password is at least 8 characters long
        if (strlen($pass) < 8) array_push($reasons, "TO_SHORT");

        //password is not longer than 50 characters
        if (strlen($pass) > 50) array_push($reasons, "TO_LONG");

        //password only contains letters(also umlaute), numbers and these special characters: # ? ! @ $ % ^ & . * - +
        if (preg_match("/^[a-zA-ZÄÖÜäöüß0-9#?!@$%^&.*\-+]*$/", $pass) !== 1) array_push($reasons, "INVALID_CHAR");

        //password contains at least one lower case letter
        if (preg_match("/[a-zäöüß]+/", $pass) !== 1) array_push($reasons, "NO_LOWER_CASE");

        //password contains at least one upper case letter
        if (preg_match("/[A-ZÄÖÜ}]+/", $pass) !== 1) array_push($reasons, "NO_UPPER_CASE");

        //password contains at least one number
        if (preg_match("/[0-9]+/", $pass) !== 1) array_push($reasons, "NO_NUMBER");

        if (sizeof($reasons) === 0) return true;
        else return $reasons;
    }

    /**
     * Validates if a string are valid words separated by spaces
     * 
     * @param  string $words        The string to validate.
     * @return true|array<string>   returns true if it is a valid combination of words, otherwise an array with reasons its not.
     */
    private function isWords(string $words): mixed
    {
        $reasons = [];

        //regex for a "word" (only letters and at least 2 characters)
        $word = "[a-zA-ZÄÖÜäöüß]{2,}";

        //one or more words with spaces between
        if (preg_match("/^(" . $word . "[ ])*" . $word . "$/", $words) !== 1) $reasons += ["NO_WORDS"];

        if (sizeof($reasons) === 0) return true;
        else return $reasons;
    }

    /**
     * Validates if a string is a valid postcode
     *
     * @param  string $postcode     The string to validate.
     * @return true|array<string>   returns true if it is a valid postcode, otherwise an array with reasons its not.
     */
    private function isPostcode(string $postcode): mixed
    {
        $reasons = [];

        //postcode need exact 5 characters
        if (strlen($postcode) !== 5) $reasons += ["INVALID_LENGTH"];

        //postcode only contains numbers
        if (preg_match("/^[0-9]+$/", $postcode) !== 1) $reasons += ["INVALID_CHAR"];

        if (sizeof($reasons) === 0) return true;
        else return $reasons;
    }

    /**
     * Validates if a string is a valid phone number
     *
     * @param  string $phone        The string to validate.
     * @return true|array<string>   returns true if it is a valid phone number, otherwise an array with reasons its not.
     */
    private function isPhoneNumber(string $phone): mixed
    {
        $reasons = [];

        $onlyNumbers = preg_replace("/[^0-9]/", "", $phone);

        //phone number is at least 10 characters long
        if (strlen($onlyNumbers) < 8) $reasons += ["TO_SHORT"];

        //phone number is not longer than 20 characters
        if (strlen($onlyNumbers) > 15) $reasons += ["TO_LONG"];

        //phone number only contains numbers, spaces and + ( ) - / . x
        if (preg_match("/^[0-9 +\-()\/.x]*$/", $phone) !== 1) $reasons += ["INVALID_CHAR"];

        if (sizeof($reasons) === 0) return true;
        else return $reasons;
    }
}
