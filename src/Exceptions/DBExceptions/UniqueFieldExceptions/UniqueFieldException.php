<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\DBExceptions\UniqueFieldExceptions;

use Exceptions\DBExceptions\DBException;

/**
 * Exception that should be thrown if trying to add a duplicate to a unique field in the database.
 */
class UniqueFieldException extends DBException
{
}
