<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Responses\Interfaces;


/**
 * Interface for ResponseCookie
 */
interface ResponseCookieInterface
{
    /**
     * Returns all the information needed to send the cookie
     * 
     * @return array<string,mixed> 
     *      $ret = [
     *          "name"      => (string)     The cookies name.
     *          "value"     => (string)     The cookies value.
     *          "expiresIn" => (int)        The Time in seconds, when the cookie will expire.
     *          "path"      => (string)     The Path, where the Cookie will be available.
     *          "secure"    => (bool)       When True the cookie is only available via a ssl connection.
     *          "httpOnly"  => (bool)       When True the cookie is only available via http.
     *      ]
     */
    public function get(): array;

    /**
     * Returns the name of the cookie
     * @return string The cookies name.
     */
    public function getName(): string;
}
