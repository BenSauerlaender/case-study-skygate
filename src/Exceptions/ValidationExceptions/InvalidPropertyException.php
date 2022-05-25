<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Exceptions\ValidationExceptions;

use Utilities\SharedUtilities;
use Throwable;

/**
 * Exception that should be thrown if a property is invalid
 */
class InvalidPropertyException extends ValidationException
{
    private array $invalidProperties;

    /**
     * @param  array<string,array<string>> $invalidProperties (propertyName => ListOfReasons)
     */
    public function __construct(array $invalidProperties, int $code = 0, ?Throwable $prev = null)
    {
        $this->invalidProperties = $invalidProperties;

        $properties = [];
        foreach ($invalidProperties as $property => $reasons) {
            $properties[$property] =  implode("+", $reasons);
        }

        $s = $this->mapped_implode(', ', $properties, ": ");

        parent::__construct("Invalid properties with Reasons: $s", $code, $prev);
    }

    public function getInvalidProperties(): array
    {
        return $this->invalidProperties;
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
