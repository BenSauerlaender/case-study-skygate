<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities\Interfaces;

// interface with several static functions to validate data
interface ValidatorInterface
{
    public static function isEmail(string $email): bool;

    public static function isPassword(string $pass): bool;

    public static function isWords(string $words): bool;

    public static function isPostcode(string $postcode): bool;

    public static function isPhoneNumber(string $phone): bool;
}
