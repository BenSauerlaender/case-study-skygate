<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

/**
 * Exception that should be thrown if something unexpected happened during password hashing
 */
class PasswordHashException extends BaseException
{
}
