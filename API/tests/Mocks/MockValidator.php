<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Mocks;

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;

// class with several static functions to validate data
class MockValidator implements ValidatorInterface
{
    private static function isNotFalse(string $input): bool
    {
        if ($input !==  "false") return true;
        else return false;
    }

    public static function isEmail(string $input): bool
    {
        return self::isNotFalse($input);
    }

    public static function isPassword(string $input): bool
    {
        return self::isNotFalse($input);
    }

    public static function isWords(string $input): bool
    {
        return self::isNotFalse($input);
    }

    public static function isPostcode(string $input): bool
    {
        return self::isNotFalse($input);
    }

    public static function isPhoneNumber(string $input): bool
    {
        return self::isNotFalse($input);
    }
}
