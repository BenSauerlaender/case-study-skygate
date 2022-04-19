<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
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

        $stmt->execute(["name" => $name]);

        $response =  $stmt->fetchAll();

        if (sizeof($response) === 0) return null;

        return $response[0]["role_id"];
    }
}
