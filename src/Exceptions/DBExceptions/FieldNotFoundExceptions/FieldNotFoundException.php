<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\DBExceptions\FieldNotFoundExceptions;

use Exceptions\DBExceptions\DBException;

/**
 * Exception that should be thrown if the specified entry cant be found in the database.
 */
class FieldNotFoundException extends DBException
{
}
