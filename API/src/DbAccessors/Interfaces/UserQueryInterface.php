<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces;


/**
 * A Class to do advanced searches on the user table
 * 
 * Abstracts all SQL statements
 */
interface UserQueryInterface
{

    /**
     * Defines a way of sorting the results
     *
     * @param  string $property        The property to sort after.
     * @param  bool   $ascending    True -> ascending. False -> decreasing
     * @throws InvalidPropertyException   if the property is not supported.
     */
    public function setSort(string $property, bool $ascending = true): void;

    /**
     * Adds the query a filter.
     * Filters out all elements, there specified property don't contain the specified search string.
     *
     * @param  string $property         The property to filter.
     * @param  string $search            The string, that the property need to contain.
     * @param  bool   $caseSensitive    True if the filter should be case sensitive, else otherwise.
     * 
     * @throws InvalidPropertyException   if the property is not supported or the search contains invalid characters.
     * 
     */
    public function addFilter(string $property, string $search, bool $caseSensitive = true): void;

    /** 
     * Runs the SQL and returns the resulted user list
     * 
     * @return array<array<string,string|int>>  A list of the found users as key-value-pairs.
     * @throws DBexception    if there is a problem with the database.
     */
    public function getResults(): array;

    /**
     * Runs the SQL and returns one page of the results.
     *
     * @param  int   $pageSize  The number of results per page
     * @param  int   $index     The index of the requested page (starting at 0)
     * @throws DBexception    if there is a problem with the database.
     */
    public function getResultsPaginated(int $pageSize, int $index): array;

    /**
     * Returns number of users that would be found by the query
     * 
     * @param int $length The length of the result list (without pagination)
     * @throws DBexception    if there is a problem with the database.
     */
    public function getLength(): int;

    /**
     * Resets all query configuration to the default values
     */
    public function reset(): void;
}
