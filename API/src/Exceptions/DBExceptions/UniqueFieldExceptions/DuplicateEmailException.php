<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions;

/**
 * Exception that is thrown if trying to add a duplicate email to a unique field in the database.
 */
class DuplicateEmailException extends UniqueFieldException
{
}
