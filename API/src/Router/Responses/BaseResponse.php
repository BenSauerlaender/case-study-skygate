<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Responses;

use BenSauer\CaseStudySkygateApi\Router\Requests\ResponseInterface;

/**
 * Base Class for API Responses
 */
abstract class BaseResponse implements ResponseInterface
{
    protected function setCode(): void
    {
    }

    protected function setCookies(): void
    {
    }

    protected function setHeaders(): void
    {
    }

    protected function setData(): void
    {
    }

    public function getCode(): int
    {
        return 0;
    }

    public function getCookies(): array
    {
        return [];
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function getData(): string
    {
        return "";
    }
}
