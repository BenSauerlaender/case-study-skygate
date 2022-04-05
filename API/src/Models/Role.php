<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\Models;

// class to represent a role
class Role {
    private int $id;
    private string $name;
    private array $rights; // array of string-bool pairs; each pair represent one right

    //simple constructor to set all properties
    public function __construct( int $id, string $name, array $rights){
        $this->id = $id;
        $this->name = $name;
        $this->rights = $rights;
    }

    public function getID() : int {
        return $this->id;
    }
}
?>