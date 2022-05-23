<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\SecurityControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\PasswordHashException;

class SecurityController implements SecurityControllerInterface
{
    public function hashPassword(string $pass): string
    {
        $ret = password_hash($pass, PASSWORD_BCRYPT);
        if (is_null($ret)) throw new  PasswordHashException("The password hash algorithm is invalid");
        if ($ret === false) throw new PasswordHashException("The password hash fails");
        return $ret;
    }

    public function checkPassword(string $pass, string $hashedPassword): bool
    {
        return password_verify($pass, $hashedPassword);
    }

    public function generateCode(int $length): string
    {
        $ret = "";

        for ($i = 0; $i < $length; $i++) {
            $num = rand(1, 9);
            $ret = $ret . "{$num}";
        }

        return $ret;
    }
}
