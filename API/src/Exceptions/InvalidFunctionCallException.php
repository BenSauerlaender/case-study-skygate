<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use Exception;

//Exception, that should be thrown if a function call don't make sense in the current state.
class InvalidFunctionCallException extends Exception
{
}
