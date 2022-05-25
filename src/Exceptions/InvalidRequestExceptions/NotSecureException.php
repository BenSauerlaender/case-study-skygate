<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\InvalidRequestExceptions;

use Exceptions\InvalidRequestExceptions\InvalidRequestException;

/**
 * Exception, that should be thrown if a connection is not secure
 */
class NotSecureException extends InvalidRequestException
{
}
