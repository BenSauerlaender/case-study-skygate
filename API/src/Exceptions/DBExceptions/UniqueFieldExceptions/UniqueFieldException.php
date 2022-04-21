<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;

/**
 * Exception that is thrown if trying to add a duplicate to a unique field in the database.
 */
class UniqueFieldException extends DBException
{
}
