<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions;

use Throwable;

/**
 * Exception that should be thrown if the specified user cant be found in the database.
 */
class UserNotFoundException extends FieldNotFoundException
{
    /**
     * Set either email (the email of the user, that was not found) OR id (the users id)
     * Set the other one to null
     */
    public function __construct(?int $id, ?string $email = null, Throwable $previous = null)
    {
        if (is_null($id)) {
            $msg = "email='$email'";
        } else {
            $msg = "id='$id'";
        }
        parent::__construct("No user with $msg found.", 0, $previous);
    }
}
