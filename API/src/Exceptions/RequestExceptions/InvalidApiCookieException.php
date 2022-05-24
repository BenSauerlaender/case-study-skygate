<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions\RequestException;
use Throwable;

//Exception, that should be thrown if an cookie is not valid
class InvalidCookieException extends RequestException
{
    public function __construct(string $cookie, Throwable $previous = null)
    {
        parent::__construct("The cookie: '$cookie' is not valid", 0, $previous);
    }
}
