<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserQueryInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;
use PDO;

/**
 * Implementation of UserQueryInterface
 */
final class MySqlUserQuery extends MySqlAccessor implements UserQueryInterface
{
    /** The Base SQL statement for all searches*/
    private const BASE_SQL = 'SELECT user_id, email, name, postcode, city, phone FROM user WHERE verified = 1';

    /** Supported properties to filter or sort*/
    public const SUPPORTED_PROPERTIES = ["email", "name", "postcode", "city", "phone"];


    /** 
     * A key value pair list. With the property to filter as key. And the case sensitivity as value
     * 
     * @param array<string,bool> 
     */
    private array $filters = [];

    /** 
     * A key value pair list. With the property to filter as key. And the search string as value
     * 
     * @param array<string,string> 
     */
    private array $sqlPlaceholders = [];

    /** The property to sort by */
    private ?string $sortBy = null;

    /** The sort direction */
    private bool $sortASC = true;


    public function setSort(string $property, bool $ascending = true): void
    {
        //lowercase the property so its not case sensitive
        $property = strtolower($property);

        //throw exception if the property is not supported
        if (!in_array($property, self::SUPPORTED_PROPERTIES)) throw new InvalidPropertyException([$property => ["NOT_SUPPORTED"]]);

        $this->sortBy = $property;
        $this->sortASC = $ascending;
    }

    public function addFilter(string $property, string $search, bool $caseSensitive = true): void
    {
        //lowercase the property so its not case sensitive
        $property = strtolower($property);

        //throw exception if the property is not supported
        if (!in_array($property, self::SUPPORTED_PROPERTIES)) throw new InvalidPropertyException([$property => ["NOT_SUPPORTED"]]);

        // '%' and '_' are not allowed because they are mysql placeholders
        if (str_contains($search, "%") or str_contains($search, "_")) throw new InvalidPropertyException([$property => ["INVALID_SYMBOL_IN_SEARCH"]]);

        //add the property to filter and the case sensitivity to the filter array
        $this->filters[$property] = $caseSensitive;

        //add the search-string to the placeholder array
        $this->sqlPlaceholders[$property] = "%$search%";
    }

    /**
     * Append the filters to the base sql-statement
     */
    private function getFilteredSql(): string
    {
        //start with the base sql statement
        $sql = self::BASE_SQL;

        //for each filter
        foreach ($this->filters as $property => $caseSensitive) {

            //if case sensitive do a LIKE BINARY comparison
            $binary = $caseSensitive ? "BINARY" : "";

            //append to the sql sql statement
            $sql = "$sql AND $property LIKE $binary :$property"; //use the property name as sql-placeholder so it can later be replaced by the search-string
        }

        return $sql;
    }

    /**
     * Append the sorting to the filtered sql-statement
     */
    private function getSortedSql(): string
    {
        //start with the filtered sql statement
        $sql = $this->getFilteredSql();

        $sortBy = $this->sortBy;

        //if a sortBy is set
        if (!is_null($sortBy)) {
            //get the sort direction
            $dir = $this->sortASC ? "ASC" : "DESC";

            //append order by to the sql statement
            $sql = "$sql ORDER BY $sortBy $dir";
        }

        return $sql;
    }

    /**
     * Append the paginating to the sorted sql-statement
     */
    private function getPaginatedSql(int $pageSize, int $index): string
    {
        //start with the filtered sql statement
        $sql = $this->getSortedSql();

        //number of entries to skip
        $skip = $pageSize * $index;

        //append the limit statement to the existing sql statement
        $sql = "$sql LIMIT $skip,$pageSize";

        return $sql;
    }

    /**
     * Takes an SQL-string, execute it, returns resulted user-array in convenient format.
     * 
     * @throws DBexception    if there is a problem with the database.
     */
    private function sqlToResults(string $sql): array
    {
        //execute the statement with the placeholders in the sqlPlaceholders array
        $stmt = $this->prepareAndExecute($sql, $this->sqlPlaceholders);

        //convert results in convenient formatted array
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //for each row/user push an key-value list to the result array
            array_push($results, [
                "id" => $row["user_id"],
                "email" => $row["email"],
                "name" => $row["name"],
                "postcode" => $row["postcode"],
                "city" => $row["city"],
                "phone" => $row["phone"],
            ]);
        }

        return $results;
    }

    public function getResults(): array
    {
        //get the constructed sql-statement
        $sql = $this->getSortedSql();

        //return the results
        return $this->sqlToResults($sql . ";"); //also add the ";" to the sql statement
    }

    public function getResultsPaginated(int $pageSize, int $index): array
    {
        //get the constructed sql-statement
        $sql = $this->getPaginatedSql($pageSize, $index);

        //return the results
        return $this->sqlToResults($sql . ";"); //also add the ";" to the sql statement
    }

    public function getLength(): int
    {
        //get the constructed sql-statement
        $sql = $this->getFilteredSql();

        //wrap into a count to only get the number of theoretical rows
        $sql = "SELECT count(*) as count FROM ( $sql ) x";

        //execute the statement
        $stmt = $this->prepareAndExecute($sql, $this->sqlPlaceholders);
        $response = $stmt->fetchAll();

        //return the count
        return $response[0]["count"];
    }

    public function reset(): void
    {
        $this->filters = [];
        $this->sqlParams = [];
        $this->sortBy = null;
        $this->sortASC = true;
    }
}
