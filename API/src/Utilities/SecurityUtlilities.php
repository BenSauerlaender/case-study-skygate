<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\SecurityUtilitiesInterface;
use RuntimeException;

class securityUtilities implements SecurityUtilitiesInterface
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
        return bin2hex(random_bytes($length / 2));
    }
}
