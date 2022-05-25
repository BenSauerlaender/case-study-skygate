<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\ValidationExceptions;

use Exceptions\BaseException;

/**
 * Exception that should be thrown if something fails a validation
 */
class ValidationException extends BaseException
{
}
