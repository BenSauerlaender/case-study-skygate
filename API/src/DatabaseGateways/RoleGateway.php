<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseGateways;

use BenSauer\CaseStudySkygateApi\Models\Role;

// class with several static functions to validate data
class RoleGateway {
    public static function findByName(string $name) : ?Role {}
}
?>
