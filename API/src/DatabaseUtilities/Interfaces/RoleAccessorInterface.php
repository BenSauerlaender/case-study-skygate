<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces;

use BenSauer\CaseStudySkygateApi\Models\Interfaces\RoleInterface;

// functions to interact with the role data recourse
interface RoleAccessorInterface
{
    public static function findByName(string $name): ?RoleInterface;
}
