<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\DatabaseInterfaces\UserInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\UserSearchQueryInterface;

// class, that help to query the user-db-table
class UserSearchQuery implements UserSearchQueryInterface
{
    private array $filters = null; //[string]-[any]-pairs that represent [table-colum]-[filter word]

    //add a filter option to the query
    public function addFilter(string $colum, mixed $filter): self
    {
        //TODO
    }
    /**
     * get one element
     * throw Exception if more or less than one element was found
     */
    public function getOne(): UserInterface
    {
        //TODO
    }
}
