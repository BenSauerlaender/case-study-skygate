<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Interfaces;

/**
 * Object that represent an api path (path to a resource)
 * 
 * An Api Path is a list of subpath's (lowercase strings only letters) and parameters (ints).
 * Parameters are at positions where the route has a placeholder.
 */
interface ApiPathInterface
{
    /**
     * Gets the path as array
     * 
     * @return array<string|int> The Path represented as array/list.
     */
    public function getArray(): array;

    /**
     * Returns the path as string with "{x}" instead of the parameters.
     * 
     * Starts with a "/" but don't end with one.
     *
     * @return string The Path represented as string.
     */
    public function getStringWithPlaceholders(): string;

    /**
     * Gets the Numbers of subpaths/parameters
     * 
     * @return int The number of path segments
     */
    public function getLength(): int;

    /**
     * Returns a List only containing the parameters (int-values)
     * 
     * @return array<int> The parameters in there original order
     */
    public function getParameters(): array;

    public function __toString(): string;
}
