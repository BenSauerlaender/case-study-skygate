<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions;

/**
 * Exception that is thrown if a field is required but not there
 */
class MissingPropertiesException extends ValidationException
{
    private array $missing;

    public function __construct(array $missing, $code = 0, $previous = null)
    {
        $this->missing = $missing;
        parent::__construct("Missing fields: " . implode(",", $missing), $code, $previous);
    }

    public function getMissing(): array
    {
        return $this->missing;
    }
}
