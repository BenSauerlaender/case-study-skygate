<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions;

use Exception;

//Exception, that should be thrown if a connection is not secure
class NotSecureException extends Exception
{
}
