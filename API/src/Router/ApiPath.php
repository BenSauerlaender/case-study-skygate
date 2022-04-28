<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;

/**
 * Class to represent an api Path
 */
class ApiPath
{
    /**
     * The Path stored as an array of strings.
     *
     * @var array<string> the path.
     */
    private array $path;

    /**
     * Constructs the path from an string.
     * 
     * Validates and stores the path.
     *
     * @param  string $s    The "/" separated path.
     */
    function __construct(string $s)
    {
        //remove leading or trailing "/"
        if (str_starts_with($s, "/")) $s = substr($s, 1);
        if (str_ends_with($s, "/")) $s = substr($s, 0, -1);

        $array = explode("/", $s);

        //validate sub-parts
        foreach ($array as $e) {
            if (preg_match("/^[a-z0-9]+$/", $e) !== 1) throw new InvalidApiPathException("The path-sub-part: '$e' contains invalid characters");
        }
        if (sizeof($array) === 0) throw new InvalidApiPathException("Path $s need to contain at least one sub-part");

        $this->path = $array;
    }

    public function get(): array
    {
        return $this->path;
    }
}
