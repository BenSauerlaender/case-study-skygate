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
     * @param  string $field        The field to sort after.
     * @param  bool   $direction    True -> ascending. False -> decreasing
     * @return BadMethodCallException   if the field is not supported.
     */
    public function setSort(string $field, bool $direction = true): void;

    /**
     * Adds the query a filter.
     * Filters out all elements, there specified field don't contain the specified match.
     *
     * @param  string $field            The field to filter.
     * @param  string $match            The string, that the field need to contain.
     * @param  bool   $caseSensitive    True if the filter should be case sensitive, else otherwise.
     * 
     * @return BadMethodCallException   if (1) the field is not supported.
     * @return BadMethodCallException   if (2) the match contains invalid characters.
     * 
     */
    public function addFilter(string $field, string $match, bool $caseSensitive = true): void;

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
}
