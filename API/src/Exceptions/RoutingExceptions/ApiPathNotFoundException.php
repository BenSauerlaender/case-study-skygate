<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions;

use Exception;

//Exception, that should be thrown if there is no route with the requested path
class ApiPathNotFoundException extends RoutingException
{
}
