<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities\Interfaces;

/**
 * Utility class for data validation
 */
interface ValidatorInterface
{
    /**
     * Validates all fields
     * 
     * @param  array<string,string> $fields  The fields to be validated.
     *  $fields = [
     *      "email"     => (string)   The users e-mail.
     *      "name"      => (string)   The users first and last name.
     *      "postcode"  => (string)   The users postcode.
     *      "city"      => (string)   The users city.
     *      "phone"     => (string)   The users phone number.
     *      "password"  => (string)   The users password.
     *  ]
     * @return true|array<string,string> True if all valid. Or the reasons why not.
     *  $reasons = [field => reasons("+"-separated)]
     * 
     * @throws UnsupportedFieldsException
     */
    public function validate(array $fields): array;
}
