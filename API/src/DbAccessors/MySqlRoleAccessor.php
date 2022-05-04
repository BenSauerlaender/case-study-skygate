<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;

// class to interact with the role-db-table
class MySqlRoleAccessor extends MySqlAccessor implements RoleAccessorInterface
{
    public function findByName(string $name): ?int
    {
        $sql = 'SELECT role_id
                FROM role
                WHERE name=:name';

        $stmt = $this->prepareAndExecute($sql, ["name" => $name]);

        $response =  $stmt->fetchAll();

        // no role found
        if (sizeof($response) === 0) return null;

        //role id of first and only row
        return $response[0]["role_id"];
    }
}
