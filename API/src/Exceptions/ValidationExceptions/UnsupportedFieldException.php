<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions;

/**
 * Exception that is thrown if a field is not supported
 */
class UnsupportedFieldException extends ValidationException
{
}
