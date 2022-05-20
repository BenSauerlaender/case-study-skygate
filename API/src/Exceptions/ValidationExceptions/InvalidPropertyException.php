<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions;

use BenSauer\CaseStudySkygateApi\Utilities\SharedUtilities;
use Throwable;

/**
 * Exception that is thrown if a field is invalid
 */
class InvalidPropertyException extends ValidationException
{
    private array $invalidFields;

    public function __construct(array $invalidFields, int $code = 0, ?Throwable $prev = null)
    {
        $this->invalidFields = $invalidFields;
        $fields = [];
        foreach ($invalidFields as $field => $reasons) {
            $fields[$field] =  implode("+", $reasons);
        }

        $s = SharedUtilities::mapped_implode(', ', $fields, ": ");
        parent::__construct("Invalid fields with Reasons: $s", $code, $prev);
    }

    public function getInvalidField(): array
    {
        return $this->invalidFields;
    }
}
