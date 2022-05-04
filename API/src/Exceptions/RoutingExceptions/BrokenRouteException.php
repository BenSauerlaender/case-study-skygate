<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions;

use BenSauer\CaseStudySkygateApi\Exceptions\ShouldNeverHappenException;

//Exception, that should be thrown if the routes array is broken. Because the array should be tested it should never happen
class BrokenRouteException extends ShouldNeverHappenException
{
}
