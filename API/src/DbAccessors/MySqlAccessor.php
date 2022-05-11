<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\FieldNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\UniqueFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ShouldNeverHappenException;
use BenSauer\CaseStudySkygateApi\Utilities\SharedUtilities;
use PDOException;
use PDOStatement;

/**
 * Super class for all MySql accessors
 * 
 * Provides a PDO object to interact with the database.
 */
class MySqlAccessor
{
    /**
     * PDO object for database interaction
     */
    private \PDO $pdo;

    /**
     * Sets the PDO object
     *
     * @param  \PDO $pdo PDO object for database interaction
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    //TODO Test this
    /**
     * Wrapper function for PDO Statement preparing and executing in one
     * 
     * @param  string       $sql        The SQL Statement with placeholders to execute.
     * @param  array        $params     The parameters to be inserted into the placeholders.
     * @return PDOStatement             The executed Statement.
     * 
     * @throws DBException  if something fails 
     *          (UniqueFieldException | FieldNotFoundException | ...)
     * 
     */
    protected function prepareAndExecute(string $sql, array $params): PDOStatement
    {
        try {
            //prepare the statement
            $stmt = $this->pdo->prepare($sql);
            if (is_null($stmt)) { // @codeCoverageIgnore
                throw new ShouldNeverHappenException("This should never happen: PDO->prepare() returned null, although the PDO error handling set to exception."); // @codeCoverageIgnore
            }

            //execute the statement
            $success = $stmt->execute($params);
            if (!$success) throw new PDOException();
        } catch (PDOException $e) {
            $this->handlePDOException($e);
        }

        return $stmt;
    }

    /**
     * Get the PDOExceptions from pdo->execute() and throws a new one
     *
     * @param  PDOException $e          The exception to handle.
     * @param  string       $sql        The SQL Statement that was prepared.
     * @param  array        $params     The parameters that were used to execute.
     * 
     * @throws DBException  always.
     *          (UniqueFieldException | FieldNotFoundException | ...)
     */
    private function handlePDOException(PDOException $e): void
    {
        //wrap DBException around PDOException
        try {
            throw new DBException("Execute PDO-Statement failed.", 0, $e);
        } catch (DBException $dbe) {

            // Duplicate field
            if (str_contains("$e", "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry")) {
                throw new UniqueFieldException("Failed to set a duplicate to a unique field", 0, $e);
            }
            //foreign key not found
            else if (str_contains("$e", "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails")) {
                throw new FieldNotFoundException("Failed to set bad foreign key", 0, $e);
            }
            //everything else
            else {
                throw $dbe; // @codeCoverageIgnore
            }
        }
    }
}
