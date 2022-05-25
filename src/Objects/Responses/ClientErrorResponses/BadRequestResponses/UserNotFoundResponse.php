<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses\ClientErrorResponses\BadRequestResponses;

use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;

/**
 * Response that should be used if the requested user don't exists.
 */
class UserNotFoundResponse extends BadRequestResponse
{
    public function __construct(?UserNotFoundException $e = null)
    {
        if (is_null($e)) {
            parent::__construct("The user not exists.", 201);
        } else {
            parent::__construct($e->getMessage(), 201);
        }
    }
}
