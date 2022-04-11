<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\PasswordUtilitiesInterface;
use RuntimeException;

class PasswordUtilities implements PasswordUtilitiesInterface
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
}
