<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities\Interfaces;

use BenSauer\CaseStudySkygateApi\Models\Interfaces\UserInterface;

// interface, that help to query the user data resource
interface UserSearchQueryInterface
{
    //add a filter option to the query
    public function addFilter(string $colum, mixed $filter): self;

    /**
     * get one element
     * throw Exception if more or less than one element was found
     */
    public function getOne(): UserInterface;

    public static function getNewInstance(): self;
}
