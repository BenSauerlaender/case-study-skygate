<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidRequestException;

//Exception, that should be thrown if an ApiPath is not valid
class InvalidPathException extends InvalidRequestException
{
}
