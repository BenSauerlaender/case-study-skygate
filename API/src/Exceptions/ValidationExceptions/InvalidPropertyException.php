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

        $s = $this->mapped_implode(', ', $fields, ": ");
        parent::__construct("Invalid fields with Reasons: $s", $code, $prev);
    }

    public function getInvalidField(): array
    {
        return $this->invalidFields;
    }

    /**
     * Utility function to implode a key value array
     * 
     * Taken from: https://www.php.net/manual/en/function.implode.php#124942 (Kommentar von "Honk der Hase")
     *
     * @param  string $glue
     * @param  array $array
     * @param  string $symbol
     */
    private function mapped_implode(string $glue, array $array, string $symbol = '=')
    {
        return implode(
            $glue,
            array_map(
                function ($k, $v) use ($symbol) {
                    return $k . $symbol . $v;
                },
                array_keys($array),
                array_values($array)
            )
        );
    }
}
