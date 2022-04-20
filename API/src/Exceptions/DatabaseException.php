<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use RuntimeException;

//Exception, that should be thrown if a function call don't make sense in the current state.
class DatabaseException extends RuntimeException
{
}
