<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Requests;

use BenSauer\CaseStudySkygateApi\Router\RequestInterface;
use BenSauer\CaseStudySkygateApi\Router\Requests\RequestMethod;
use BenSauer\CaseStudySkygateApi\Router\Requests\Interfaces\RequestPathInterface;
use BenSauer\CaseStudySkygateApi\Router\Requests\Interfaces\RequestQueryInterface;

/**
 * Class that represent an request to the API
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
