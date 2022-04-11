<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\Models\Interfaces\RoleInterface;

// class to interact with the role-db-table
class MySqlRoleAccessor extends MySqlAccessor implements RoleAccessorInterface
{
}
