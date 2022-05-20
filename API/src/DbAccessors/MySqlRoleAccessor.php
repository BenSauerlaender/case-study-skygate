<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;

/**
 * Implementation of RoleAccessorInterface
 */
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

    public function get(int $id): array
    {
        $sql = 'SELECT *
                FROM role
                WHERE role_id=:id';

        $stmt = $this->prepareAndExecute($sql, ["id" => $id]);

        $response =  $stmt->fetchAll();

        // no role found
        if (sizeof($response) === 0) throw new RoleNotFoundException("RoleID: $id");

        //get first and only role
        $response = $response[0];

        return [
            "id" => $id,
            "name" => $response["name"],
            "permissions" => $response["permissions"],
            "createdAt" => $response["created_at"],
            "updatedAt" => $response["updated_at"]
        ];
    }

    public function getList(): array
    {
        $sql = 'SELECT name
                FROM role;';

        $stmt = $this->prepareAndExecute($sql, []);

        $ret = [];

        //go through each row and add the name to the $ret array
        $element = $stmt->fetchColumn();
        while ($element != FALSE) { //while there is another row
            array_push($ret, $element);

            $element = $stmt->fetchColumn(); //get the next row
        }

        //return the list
        return $ret;
    }
}
