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
     * Validates all attributes
     * 
     * Checks if all attributes can be validated.
     * Validates all attributes.
     *
     * @param  array<string,string> $attr  The attributes to be validated.
     *  $attr = [
     *      "email"     => (string)   The users e-mail.
     *      "name"      => (string)   The users first and last name.
     *      "postcode"  => (string)   The users postcode.
     *      "city"      => (string)   The users city.
     *      "phone"     => (string)   The users phone number.
     *      "password"  => (string)   The users password.
     *  ]
     * 
     * @throws InvalidArgumentException (1)       if a attribute can't be validated.
     * @throws InvalidAttributeException (1-6)    if a attribute fails the validation.
     */
    public function validate(array $attr): void;
}
