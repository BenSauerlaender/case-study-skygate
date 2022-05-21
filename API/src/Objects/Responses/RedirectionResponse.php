<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses;

use BenSauer\CaseStudySkygateApi\Exceptions\JsonException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseCodeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseHeaderException;
use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ResponseCookieInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ApiResponseInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\BaseException;

/**
 * Response to use if the requester should be redirected to another url after a successful request.
 */
final class RedirectionResponse extends BaseResponse
{
    public function __construct(string $url)
    {
        $this->setCode(303);
        $this->addHeader("Location", $url);
    }
}
