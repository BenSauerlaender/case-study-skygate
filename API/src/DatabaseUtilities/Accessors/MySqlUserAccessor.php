<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\UserAccessorInterface;

// class to interact with the user-db-table
class MySqlUserAccessor extends MySqlAccessor implements UserAccessorInterface
{
}
