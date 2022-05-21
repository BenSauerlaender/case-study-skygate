<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Interfaces;

/**
 * Interface for ApiPath
 * 
 * An Api Path is a list of subpath's (lowercase strings only letters) instead of a subpath it can also be an id (int value).
 * Id's are at positions where the route has placeholder.
 */
interface ApiPathInterface
{
    /**
     * Gets the path as array
     * 
     * @return array<string|int> The Path represented as array
     */
    public function getArray(): array;

    /**
     * Returns the path as string with "{id}" instead of the ids.
     * 
     * Starts with a / but don't end with one
     *
     * @return string
     */
    public function getStringWithPlaceholders(): string;

    /**
     * Gets the length of the 
     * 
     * @return int The number of path segments
     */
    public function getLength(): int;

    /**
     * Returns an Array only containing the id's (int-values)
     * 
     * @return array<int> The id's in there original order
     */
    public function getIDs(): array;
}
