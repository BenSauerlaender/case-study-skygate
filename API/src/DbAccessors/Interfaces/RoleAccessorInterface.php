<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces;

use BenSauer\CaseStudySkygateApi\Models\Interfaces\RoleInterface;

/**
 * Accessor for the role database table
 * 
 * Abstracts all SQL statements
 */
interface RoleAccessorInterface
{
    /**
     * Finds the role by giving name
     *
     * @param  string   $name   The roles name.
     * @return null|int         The roles id (or null if the role cant be found).
     * 
     * @throws DBException if there is a problem with the database.
     */
    public function findByName(string $name): ?int;

    /**
     * Gets the roles entry
     *
     * @param  int   $id    The roles id.
     * @return array<string,string|int> The $role array.
     *  $role = [
     *      "id"                => (int)        The roles id. 
     *      "name"              => (string)     The roles name.
     *      "permissions"       => (string)     The roles permissions.
     *      "createdAt"         => (string)     The DateTime the user was created.
     *      "updatedAt"         => (string)     The last DateTime the user was updated.
     *  ]
     * 
     * @throws DBException if there is a problem with the database.
     *          (RoleNotFoundException | ...)
     */
    public function get(int $id): array;
    /**
     * Gets a list of all roles
     * 
     * @return array<string> An array containing all role names.
     * @throws DBException if there is a problem with the database.
     */
    public function getList(): array;
}
