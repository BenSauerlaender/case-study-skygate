<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests;

use BenSauer\CaseStudySkygateApi\Router\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiPathInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\RequestQueryInterface;

/**
 * Class that represent an Request to the API
 */
class Request implements ApiRequestInterface
{
    function __construct(
        ApiPathInterface $path,
        ApiMethod $method,
        RequestQueryInterface $query,
        RequestCookiesInterface $cookies,
        RequestHeadersInterface $headers
    ) {
    }
}
