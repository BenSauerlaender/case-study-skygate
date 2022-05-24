<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions\RequestException;

//Exception, that should be thrown if an query string is not valid
class InvalidQueryException extends RequestException
{
}
