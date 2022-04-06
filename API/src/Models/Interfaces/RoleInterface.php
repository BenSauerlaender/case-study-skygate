<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Models\Interfaces;

// interface to represent a role
interface RoleInterface
{

    //simple constructor to set all properties
    public function __construct(int $id, string $name, array $rights);

    public function getID(): int;
}
