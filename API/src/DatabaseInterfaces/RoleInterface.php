<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseInterfaces;

use BenSauer\CaseStudySkygateApi\Models\Role;

// functions to interact with the role-db-table
class RoleInterface {
    public static function findByName(string $name) : ?Role {}
}
?>
