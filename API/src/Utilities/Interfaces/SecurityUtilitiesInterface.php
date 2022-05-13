<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities\Interfaces;

interface SecurityUtilitiesInterface
{
    /**
     * Hashes the given password.
     *
     * @param  string $pass The Password to hash.
     * @return string       returns the hashed password.
     */
    public function hashPassword(string $pass): string;

    /**
     * Checks if the given password is correct
     *
     * @param  string $pass             The password to check.
     * @param  string $hashedPassword   The hashed password correct password.
     * @return bool                     returns true if the password is correct. False otherwise.
     */
    public function checkPassword(string $pass, string $hashedPassword): bool;

    /**
     * Generates a semi random number string
     *
     * @param  int    $length   The length of the output string. Valid between 0 and 99.
     * @return string           returns a string out of digits(0-9).
     */
    public function generateCode(int $length): string;
}
