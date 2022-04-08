<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities\Interfaces;

interface PasswordUtilitiesInterface
{
    //hash the specified password
    public static function hashPassword(string $pass): string;

    //returns true if password is correct; otherwise: false
    public static function checkPassword(string $pass, string $hashedPassword): bool;
}
