<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Mocks;

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\UserSearchQueryInterface;

class MockUserSearchQuery implements UserSearchQueryInterface
{
    public ?array $filters = null;

    //add a filter option to the query
    public function addFilter(string $colum, mixed $filter): self
    {
        $filter = array($colum => $filter);
        return $this;
    }

    public function getOne(): MockUser
    {
        return new MockUser(0, "", "", "", "", "", "", true, null, 0);
    }

    public static function getNewInstance(): self
    {
        return new self();
    }
}
