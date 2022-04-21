<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;

/**
 * Exception that is thrown if the specified entry cant be found in the database.
 */
class FieldNotFoundException extends DBException
{
}
