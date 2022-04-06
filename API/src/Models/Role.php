<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Models;

use BenSauer\CaseStudySkygateApi\Models\Interfaces\RoleInterface;

// class to represent a role
class Role extends RoleInterface
{
    private int $id;
    private string $name;

    // array of string-bool pairs; each pair represent one right
    private array $rights;

    //simple constructor to set all properties
    public function __construct(int $id, string $name, array $rights)
    {
        $this->id = $id;
        $this->name = $name;
        $this->rights = $rights;
    }

    public function getID(): int
    {
        return $this->id;
    }
}
