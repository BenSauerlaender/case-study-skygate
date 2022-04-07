<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use Exception;

//Exception, that should be thrown if a dependency, that is necessary is missing.
class MissingDependencyException extends Exception
{
}
