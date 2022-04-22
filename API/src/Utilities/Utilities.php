<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

class Utilities
{
    //TODO Test this
    /**
     * Utility function to implode a key value array
     * 
     * Taken from: https://www.php.net/manual/en/function.implode.php#124942 (Kommentar von "Honk der Hase")
     *
     * @param  string $glue
     * @param  array $array
     * @param  string $symbol
     */
    static public function mapped_implode(string $glue, array $array, string $symbol = '=')
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
