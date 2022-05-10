<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions;

use Exception;
use BenSauer\CaseStudySkygateApi\Exceptions\BaseException;

/**
 * Exception that is thrown if an unsupported response header will try to be set
 */
class UnsupportedResponseHeaderException extends BaseException
{
}
