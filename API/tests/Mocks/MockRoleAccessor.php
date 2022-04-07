<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Mocks;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\Models\Interfaces\RoleInterface;

class MockRoleAccessor implements RoleAccessorInterface
{
    public static function findByName(string $name): ?RoleInterface
    {
        if ($name === "true") {
            return new MockRole(0, "validRole", []);
        } else return null;
    }
}

class MockRole implements RoleInterface
{
    public function __construct(int $id, string $name, array $a)
    {
    }
    public function getID(): int
    {
        return 0;
    }
}
