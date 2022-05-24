<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use Throwable;

/**
 * Exception that should be thrown in a case that should not be possible
 */
class ShouldNeverHappenException extends BaseException
{
    public function __construct(string $reason, Throwable $previous = null)
    {
        parent::__construct("This should never happen, because: $reason", 0, $previous);
    }
}
