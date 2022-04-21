<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions;

use Exception;

/**
 * Exception that is thrown if something unexpected happened with the database
 */
class DBException extends Exception
{
}
