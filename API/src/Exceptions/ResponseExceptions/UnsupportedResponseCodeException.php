<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\BaseException;
use Exception;

/**
 * Exception that is thrown if an unsupported response code will try to be set
 */
class UnsupportedResponseCodeException extends BaseException
{
}
