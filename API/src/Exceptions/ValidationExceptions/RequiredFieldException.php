<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ValidationExceptions;

/**
 * Exception that is thrown if a field is required but not there
 */
class RequiredFieldException extends ValidationException
{
}
