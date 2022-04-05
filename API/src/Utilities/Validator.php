<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

// class with several static functions to validate data
class Validator {

    public static function isEmail(string $email) : bool {
        //email's longer than 100 characters are not allowed
        if(strlen($email) > 100 ) return false;

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isPassword(string $pass) : bool {
        //password is at least 8 characters long
        if(strlen($pass) < 8 ) return false;
        
        //password is not longer than 50 characters
        if(strlen($pass) > 50 ) return false;

        //password only contains letters(also umlaute), numbers and these special characters: # ? ! @ $ % ^ & . * - +
        if(preg_match("^[a-zA-Z0-9#?!@$%^&.*\-+\u00c4\u00e4\u00d6\u00f6\u00dc\u00fc\u00df]*$", $pass) !== 1) return false;
        
        //password contains at least one lower case letter
        if(preg_match("[a-z\u00e4\u00f6\u00fc\u00df]", $pass) !== 1) return false;
        
        //password contains at least one upper case letter
        if(preg_match("[A-Z\u00c4\u00d6\u00dc]", $pass) !== 1) return false;
        
        //password contains at least one number
        if(preg_match("[0-9]", $pass) !== 1) return false;

        return true;

    }

    public static function isWords(string $words) : bool {
        //regex for a "word" (only letters and at least 2 characters)
        $word = "[a-zA-Z\u00c4\u00e4\u00d6\u00f6\u00dc\u00fc\u00df]{2,}"; 
        
        //one or more words with spaces between
        if(preg_match("^(" . $word . "[ ]?)*" . $word . "$", $words) !== 1) return false;

        return true;
    }

    public static function isPostcode(string $postcode) : bool {
        //postcode need exact 5 characters
        if(strlen($postcode) !== 5 ) return false;

        //postcode only contains numbers
        if(preg_match("^[0-9]$", $postcode) !== 1) return false;

        return true;
    }

    public static function isPhoneNumber(string $phone) : bool {
        //phonenumber is at least 10 characters long
        if(strlen($phone) < 10 ) return false;
        
        //phonenumber is not longer than 20 characters
        if(strlen($phone) > 20 ) return false;

        //phonenumber only contains numbers, spaces and + ( ) - / 
        if(preg_match("^[0-9 +\-()/]*$", $phone) !== 1) return false;
        
        return true;
    }
}
?>