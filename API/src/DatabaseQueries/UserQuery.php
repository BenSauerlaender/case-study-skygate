<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseQueries;

use BenSauer\CaseStudySkygateApi\Models\User;

// class, that help to querry the user-db-table
class UserQuery {
    private array $filters = null; //[string]-[any]-pairs that represent [table-colum]-[filter word]

    public function addFilter(string $colum, mixed $filter) : UserQuery{
        //TODO
    }
    public function getOne() : User {
        //TODO
    }
}
?>

