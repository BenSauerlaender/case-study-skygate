<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DatabaseException;
use PDOException;
use RuntimeException;

// class to interact with the role-db-table
class MySqlRoleAccessor extends MySqlAccessor implements RoleAccessorInterface
{
    public function findByName(string $name): ?int
    {
        $stmt = $this->pdo->prepare('
            SELECT role_id
            FROM role
            WHERE name=:name
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            $stmt->execute(["name" => $name]);
        } catch (PDOException $e) {
            throw new DatabaseException("", 1, $e);
        }

        $response =  $stmt->fetchAll();

        // no role found
        if (sizeof($response) === 0) return null;

        //role id of first and only row
        return $response[0]["role_id"];
    }
}
