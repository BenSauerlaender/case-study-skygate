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
}
