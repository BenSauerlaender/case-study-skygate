<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller\Interfaces;

/**
 * Controller for data validation
 */
interface ValidationControllerInterface
{
    /**
     * Validates all properties
     * 
     * @param  array<string,string> $properties  The properties to be validated.
     *  $properties = [
     *      "email"     => (string)   The users e-mail.
     *      "name"      => (string)   The users first and last name.
     *      "postcode"  => (string)   The users postcode.
     *      "city"      => (string)   The users city.
     *      "phone"     => (string)   The users phone number.
     *      "password"  => (string)   The users password.
     *  ]
     * @return true|array<string,string> True if all valid. Or the reasons why not.
     *  $reasons = [property => reasons("+"-separated)]
     * 
     */
    public function validate(array $properties): mixed;
}
