<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests;

use BenSauer\CaseStudySkygateApi\Router\RequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\RequestMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\RequestPathInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\RequestQueryInterface;

/**
 * Class that represent an Request to the API
 */
class Request implements RequestInterface
{
    function __construct(
        RequestPathInterface $path,
        RequestMethod $method,
        RequestQueryInterface $query,
        RequestCookiesInterface $cookies,
        RequestHeadersInterface $headers
    ) {
    }
}
