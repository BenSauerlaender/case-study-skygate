<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;
use BenSauer\CaseStudySkygateApi\Objects\Cookies\Interfaces\CookieInterface;

//Simple Helper Class to create a dummy Cookie
final class SimpleResponseCookie implements CookieInterface
{
    private $name;
    private $value;
    private $expiresIn;
    private $path;
    private $secure;
    private $httponly;

    function __construct(string $name, string $value, int $expiresIn, string $path, bool $secure, bool $httponly)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expiresIn = $expiresIn;
        $this->path = $path;
        $this->secure = $secure;
        $this->httponly = $httponly;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function get(): array
    {
        return [

            "name" => $this->name,
            "value" => $this->value,
            "expiresIn" => $this->expiresIn,
            "path" => $this->path,
            "secure" => $this->secure,
            "httpOnly" => $this->httponly
        ];
    }
}

//Simple Helper Class to create a dummy Response
final class SimpleResponse extends BaseResponse
{
    public function setCode(int $code): void
    {
        parent::setCode($code);
    }

    public function addCookie(CookieInterface
    $cookie): void
    {
        parent::addCookie($cookie);
    }

    public function addHeader(string $name, string $value): void
    {
        parent::addHeader($name, $value);
    }

    public function setBody(array $data): void
    {
        parent::setBody($data);
    }
}
