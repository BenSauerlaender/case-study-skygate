<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities\Interfaces;

interface PasswordUtilitiesInterface
{
    /**
     * Hashes the given password.
     *
     * @param  string $pass The Password to hash.
     * @return string Returns the hashed password.
     */
    public function hashPassword(string $pass): string;

    /**
     * Checks if the given password is correct
     *
     * @param  string $pass             The password to check.
     * @param  string $hashedPassword   The hashed password correct password.
     * @return bool Returns true if the password is correct. False otherwise.
     */
    public function checkPassword(string $pass, string $hashedPassword): bool;
}
