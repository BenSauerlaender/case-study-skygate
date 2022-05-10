<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents;

use BenSauer\CaseStudySkygateApi\ApiComponents\Interfaces\ApiPathInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;

/**
 * Class that implements ApiPathInterface
 */
class ApiPath implements ApiPathInterface
{
    /**
     * The Path stored as an array of strings or ints.
     *
     * @var array<string|int> the path.
     */
    private array $path;

    /**
     * Only the ids (int's) from the path.
     *
     * @var array<int> the ids.
     */
    private array $ids;

    /**
     * Constructs the path from an string.
     * 
     * Validates and stores the path.
     *
     * @param  string $s    The "/" separated path.
     * 
     * @throws InvalidApiPathException
     */
    function __construct(string $s)
    {
        //remove leading or trailing "/"
        if (str_starts_with($s, "/")) $s = substr($s, 1);
        if (str_ends_with($s, "/")) $s = substr($s, 0, -1);

        //cut by / and make lower case
        $array = explode("/", strtolower($s));

        //empty path is not valid
        if (sizeof($array) === 0) throw new InvalidApiPathException("Path $s need to contain at least one sub-part");

        $path = [];
        $ids = [];

        //go through each subpath
        foreach ($array as $e) {
            if (preg_match("/^[a-z]+$/", $e) === 1) {
                //if only letters:
                array_push($path, $e);
            } else if (preg_match("/^[0-9]+$/", $e) === 1) {
                //if only numbers its an id: save as int
                array_push($path, (int)$e);
                array_push($ids, (int)$e);
            } else {
                //the subpath and so the whole path is invalid
                throw new InvalidApiPathException("The path-sub-part: '$e' contains invalid characters");
            }
        }

        $this->path = $path;
        $this->ids = $ids;
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

            //append {id} if its an id otherwise the subpath itself
            if (is_int($sub)) {
                $ret = $ret . "{id}";
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

    public function getIDs(): array
    {
        return $this->ids;
    }

    public function __toString(): string
    {
        return "/" . implode("/", $this->path);
    }
}
