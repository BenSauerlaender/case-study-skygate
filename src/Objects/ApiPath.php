<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects;

use BenSauer\CaseStudySkygateApi\Objects\Interfaces\ApiPathInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidPathException;

/**
 * Class that implements ApiPathInterface
 */
class ApiPath implements ApiPathInterface
{
    /**
     * The Path stored as an array of strings (subpaths) or ints (parameters).
     *
     * @var array<string|int> the path.
     */
    private array $path;

    /**
     * Only the parameters (int's) from the path.
     *
     * @var array<int> the parameters.
     */
    private array $parameters;

    /**
     * Constructs the api path from an string.
     * 
     * Validates and stores the path.
     *
     * @param  string $s    The "/" separated path.
     * 
     * @throws InvalidPathException if the string can not be validated as api path
     */
    public function __construct(string $s)
    {
        //remove leading and trailing "/"
        if (str_starts_with($s, "/")) $s = substr($s, 1);
        if (str_ends_with($s, "/")) $s = substr($s, 0, -1);

        //cut by / and make lower case
        $array = explode("/", strtolower($s));

        //empty path is not valid
        if (sizeof($array) === 0) throw new InvalidPathException("Path $s need to contain at least one sub-part");

        $path = [];
        $parameters = [];

        //go through each subpath
        foreach ($array as $e) {
            if (preg_match("/^[a-z]+$/", $e) === 1) {
                //if only letters:
                array_push($path, $e);
            } else if (preg_match("/^[0-9]+$/", $e) === 1) {
                //if only numbers its an parameter: save as int
                array_push($path, (int)$e);
                array_push($parameters, (int)$e);
            } else {
                //the subpath and so the whole path is invalid
                throw new InvalidPathException("The path-sub-part: '$e' contains invalid characters");
            }
        }

        $this->path = $path;
        $this->parameters = $parameters;
    }

    public function getArray(): array
    {
        return $this->path;
    }

    public function getStringWithPlaceholders(): string
    {
        $ret =  "";

        //go through each subpath
        foreach ($this->path as $sub) {
            //append a /
            $ret = $ret . "/";

            //append {x} (the placeholder symbol) if its an parameter, otherwise the subpath itself
            if (is_int($sub)) {
                $ret = $ret . "{x}";
            } else {
                $ret = $ret . $sub;
            }
        }

        return $ret;
    }

    public function getLength(): int
    {
        return sizeof($this->path);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function __toString(): string
    {
        return "/" . implode("/", $this->path);
    }
}
