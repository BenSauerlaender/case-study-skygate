<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use Exception;
use Throwable;

/**
 * Exception that is thrown if a invalid ApiMethod occurs
 */
class InvalidApiMethodException extends BaseException
{
    public function __construct(string $method, Throwable $previous = null)
    {
        parent::__construct("The method: $method is not supported", 0, $previous);
    }
}
