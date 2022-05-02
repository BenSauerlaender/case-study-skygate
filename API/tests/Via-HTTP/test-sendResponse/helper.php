<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Router\Responses\BaseResponse;
use BenSauer\CaseStudySkygateApi\Router\Responses\Interfaces\ResponseCookieInterface;

//Create a new SimpleResponse class to create simple and dynamic Responses to test the sendResponse function 
final class SimpleResponse extends BaseResponse
{
    public function setCode(int $code): void
    {
        parent::setCode($code);
    }

    public function addCookie(ResponseCookieInterface $cookie): void
    {
        parent::addCookie($cookie);
    }

    public function addHeader(string $name, string $value): void
    {
        parent::addHeader($name, $value);
    }

    public function setData(array $data): void
    {
        parent::setData($data);
    }
}
