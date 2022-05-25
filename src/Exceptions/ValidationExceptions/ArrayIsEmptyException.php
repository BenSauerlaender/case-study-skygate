<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\ValidationExceptions;

/**
 * Exception that should be thrown if an array is empty, that should not
 */
class ArrayIsEmptyException extends ValidationException
{
}
