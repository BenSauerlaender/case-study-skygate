<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\DBExceptions\FieldNotFoundExceptions;

use Throwable;

/**
 * Exception that should be thrown if a request cant be found in the database.
 */
class EcrNotFoundException extends FieldNotFoundException
{
    public function __construct(int $id, string $idName = "ecrID", Throwable $previous = null)
    {
        parent::__construct("No request with $idName=$id found.", 0, $previous);
    }
}
