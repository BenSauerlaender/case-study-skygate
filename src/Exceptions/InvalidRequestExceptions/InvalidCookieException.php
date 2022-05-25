<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\InvalidRequestExceptions;

use Exceptions\InvalidRequestExceptions\InvalidRequestException;
use Throwable;

/**
 * Exception, that should be thrown if a cookie is not valid
 */
class InvalidCookieException extends InvalidRequestException
{
    public function __construct(string $cookie, Throwable $previous = null)
    {
        parent::__construct("The cookie: '$cookie' is not valid", 0, $previous);
    }
}
