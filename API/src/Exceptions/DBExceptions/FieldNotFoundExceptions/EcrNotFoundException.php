<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions;

/**
 * Exception that is thrown if the specified request cant be found in the database.
 */
class EcrNotFoundException extends FieldNotFoundException
{
}
