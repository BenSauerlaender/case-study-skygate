<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\DBExceptions;

use Exceptions\BaseException;

/**
 * Exception that should be thrown if something unexpected happened with the database
 */
class DBException extends BaseException
{
}
