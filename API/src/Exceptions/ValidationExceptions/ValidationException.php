<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\BaseException;
use Exception;

/**
 * Exception that is thrown if something is not valid
 */
class ValidationException extends BaseException
{
}
