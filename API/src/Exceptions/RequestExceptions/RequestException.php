<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\BaseException;

//Exception, that should be thrown if an Api Request is unexpected
class RequestException extends BaseException
{
}
