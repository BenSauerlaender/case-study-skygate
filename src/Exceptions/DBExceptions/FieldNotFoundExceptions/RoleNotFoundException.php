<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions;

use Throwable;

/**
 * Exception that should be thrown if the specified role cant be found in the database.
 */
class RoleNotFoundException extends FieldNotFoundException
{
    /**
     * Set either name (the name of the role, that was not found) OR id (the roles id)
     * Set the other one to null
     */
    public function __construct(?int $id, ?string $name = null, Throwable $previous = null)
    {
        if (is_null($id)) {
            $msg = "name='$name'";
        } else {
            $msg = "id='$id'";
        }
        parent::__construct("No role with $msg found.", 0, $previous);
    }
}
