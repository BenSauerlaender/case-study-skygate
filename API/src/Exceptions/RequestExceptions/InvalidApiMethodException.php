<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions\RequestException;
use Throwable;

/**
 * Exception that is thrown if a invalid ApiMethod occurs
 */
class InvalidMethodException extends RequestException
{
    public function __construct(string $method, Throwable $previous = null)
    {
        parent::__construct("The method: $method is not a valid http Method", 0, $previous);
    }
}
