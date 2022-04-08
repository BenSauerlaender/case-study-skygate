<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\Models\Interfaces\RoleInterface;

// functions to interact with the role-db-table
class MySqlRoleAccessor extends MySqlAccessor implements RoleAccessorInterface
{
    //find a role by its name and return a role object
    public function findByName(string $name): ?RoleInterface
    {
    }
}
