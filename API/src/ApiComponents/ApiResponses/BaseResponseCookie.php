<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ResponseCookieInterface;

/**
 * Base Class to represent an Cookie that can be send to the client
 */
abstract class BaseResponseCookie implements ResponseCookieInterface
{

    private string $name;
    private string $value;
    private int $expiresIn;
    private string $path;
    private bool $secure;
    private bool $httpOnly;


    public function __construct(string $name, string $value, int $expiresIn, string $path, bool $secure, bool $httpOnly)
    {

        $this->name = $name;
        $this->value = $value;
        $this->expiresIn = $expiresIn;
        $this->path = $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    public function get(): array
    {
        return [
            "name"      => $this->name,
            "value"     => $this->value,
            "expiresIn" => $this->expiresIn,
            "path"      => $this->path,
            "secure"    => $this->secure,
            "httpOnly"  => $this->httpOnly
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
