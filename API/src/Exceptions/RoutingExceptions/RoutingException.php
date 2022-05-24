<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\BaseException;
use Exception;

/**
 * Exception, that should be thrown if the Api routing fails
 */
class RoutingException extends BaseException
{
}
