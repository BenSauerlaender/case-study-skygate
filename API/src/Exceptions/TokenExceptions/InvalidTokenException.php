<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\BaseException;

/**
 * Exception, that should be thrown if a token is not valid.
 */
class InvalidTokenException extends BaseException
{
}
