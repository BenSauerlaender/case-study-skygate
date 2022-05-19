<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserQueryInterface;
use PDO;

final class MySqlUserQuery extends MySqlAccessor implements UserQueryInterface
{
    private const BASE_SQL = 'SELECT user_id, email, name, postcode, city, phone FROM user WHERE verified = 1';
    private const FIELDS = ["email", "name", "postcode", "city", "phone"];


    /** @param array<array<string,bool|string>> */
    private array $filters = [];

    private array $sqlParams = [];

    private ?string $sortBy = null;
    private bool $sortDir = true;

    public function setSort(string $field, bool $direction = true): void
    {
        $lowField = strtolower($field);
        if (!in_array($lowField, self::FIELDS)) throw new BadMethodCallException("The field is not supported");

        $this->sortBy = $lowField;
        $this->sortDir = $direction;
    }

    public function addFilter(string $field, string $match, bool $caseSensitive = true): void
    {
        $lowField = strtolower($field);
        if (!in_array($lowField, self::FIELDS)) throw new BadMethodCallException("The field is not supported", 1);

        if (str_contains($match, "%") or str_contains($match, "_")) throw new BadMethodCallException("The match-string contains invalid symbols", 2);

        array_push($this->filters, [
            "case" => $caseSensitive,
            "field" => $lowField,
            "match" => $match
        ]);
    }

    private function getFilteredSql(): string
    {
        $this->sqlParams = [];

        $sql = self::BASE_SQL;

        foreach ($this->filters as $filter) {
            $caseSensitive = $filter["case"] ? "BINARY" : "";
            $field = $filter["field"];
            $match = $filter["match"];

            $sql = "$sql AND $field LIKE $caseSensitive :$field";

            $this->sqlParams[$field] = "%$match%";
        }
        return $sql;
    }

    private function getSortedSql(): string
    {
        $sql = $this->getFilteredSql();
        $sortBy = $this->sortBy;
        if (!is_null($sortBy)) {
            $dir = $this->sortDir ? "ASC" : "DESC";
            $sql = "$sql ORDER BY $sortBy $dir";
        }
        return $sql;
    }

    private function getPaginatedSql(int $pageSize, int $index): string
    {
        $sql = $this->getSortedSql();
        $skip = $pageSize * $index;
        $sql = "$sql LIMIT $skip,$pageSize";
        return $sql;
    }

    /**
     * Takes an SQL-string, execute it, returns resulted user-array in continent format.
     * @throws DBexception    if there is a problem with the database.
     */
    private function sqlToResults(string $sql): array
    {
        //execute the statement with the parameters in the sqlParam array
        $stmt = $this->prepareAndExecute($sql, $this->sqlParams);

        //convert results in convenient formatted array
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //for each row/user push an key-value array to the result array
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
        return $this->sqlToResults($sql . ";");
    }

    public function getResultsPaginated(int $pageSize, int $index): array
    {
        //get the constructed sql-statement
        $sql = $this->getPaginatedSql($pageSize, $index);

        //return the results
        return $this->sqlToResults($sql . ";");
    }

    public function getLength(): int
    {
        //get the constructed sql-statement
        $sql = $this->getFilteredSql();

        //wrap into a count
        $sql = "SELECT count(*) as count FROM ( $sql ) x";

        //execute the statement
        $stmt = $this->prepareAndExecute($sql, $this->sqlParams);
        $response = $stmt->fetchAll();

        //return the count
        return $response[0]["count"];
    }
}
