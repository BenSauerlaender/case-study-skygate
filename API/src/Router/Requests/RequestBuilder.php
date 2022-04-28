<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Requests;

use BenSauer\CaseStudySkygateApi\Router\RequestInterface;

/**
 * Class to build a request
 */
class RequestBuilder
{
    /**
     * Builds an request based on the $_SERVER and $_COOKIE arrays provided by php
     *
     * @param  array  $server           The $_SERVER array
     * @param  array  $cookie           The $_COOKIE array
     * @param  string $PATH_PREFIX      The prefix to cut in front of path
     */
    static public function build(array $server, array $cookie, string $PATH_PREFIX): RequestInterface
    {
    }
}
