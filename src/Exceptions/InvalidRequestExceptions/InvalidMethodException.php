<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidRequestException;
use Throwable;

/**
 * Exception that should be thrown if an invalid ApiMethod occurs
 */
class InvalidMethodException extends InvalidRequestException
{
    public function __construct(string $method, Throwable $previous = null)
    {
        parent::__construct("The method: $method is not a valid http Method", 0, $previous);
    }
}
