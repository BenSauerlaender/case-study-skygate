<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions;

use Throwable;

/**
 * Exception that should be thrown if trying to add a duplicate email to a unique field in the database.
 */
class DuplicateEmailException extends UniqueFieldException
{
    public function __construct(string $email, Throwable $previous = null)
    {
        parent::__construct("email=$email is already taken.", 0, $previous);
    }
}
