<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\SecurityUtilitiesInterface;
use OutOfRangeException;
use RuntimeException;

class SecurityUtilities implements SecurityUtilitiesInterface
{
    public function hashPassword(string $pass): string
    {
        $ret = password_hash($pass, PASSWORD_BCRYPT);
        if (is_null($ret)) throw new RuntimeException("The password hash algorithm is invalid");
        if ($ret === false) throw new RuntimeException("The password hash fails");
        return $ret;
    }

    public function checkPassword(string $pass, string $hashedPassword): bool
    {
        return password_verify($pass, $hashedPassword);
    }

    public function generateCode(int $length): string
    {
        if ($length < 0 or $length > 99) {
            throw new OutOfRangeException("The length " . $length . " is not between 0 and 99", 1);
        }

        if ($length === 0) return "";

        $code = bin2hex(random_bytes((int)ceil($length / 2)));

        //cut the last character if length is uneven
        return substr($code, 0, $length);
    }
}
