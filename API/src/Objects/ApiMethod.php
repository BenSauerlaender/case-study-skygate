<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiMethodException;

/**
 * An enum to represent valid methods for an Request
 */
enum ApiMethod
{
    case GET;
    case POST;
    case HEAD;
    case PUT;
    case DELETE;
    case CONNECT;
    case OPTIONS;
    case TRACE;
    case PATCH;

    public function toString(): string
    {
        return match ($this) {
            ApiMethod::GET => "GET",
            ApiMethod::POST => "POST",
            ApiMethod::HEAD => "HEAD",
            ApiMethod::PUT => "PUT",
            ApiMethod::DELETE => "DELETE",
            ApiMethod::CONNECT => "CONNECT",
            ApiMethod::OPTIONS => "OPTIONS",
            ApiMethod::TRACE => "TRACE",
            ApiMethod::PATCH => "PATCH"
        };
    }

    /**
     * Returns the correct HttpMethod from an string
     * 
     * Case insensitive
     *
     * @param  string $s                    The string to evaluate.
     * 
     * @throws InvalidApiMethodException    if the string cant be evaluated.
     */
    static function fromString(string $s): self
    {
        switch (strtoupper($s)) {
            case "GET":
                return self::GET;
                break;
            case "POST":
                return self::POST;
                break;
            case "HEAD":
                return self::HEAD;
                break;
            case "PUT":
                return self::PUT;
                break;
            case "DELETE":
                return self::DELETE;
                break;
            case "CONNECT":
                return self::CONNECT;
                break;
            case "OPTIONS":
                return self::OPTIONS;
                break;
            case "TRACE":
                return self::TRACE;
                break;
            case "PATCH":
                return self::PATCH;
                break;
            default:
                throw new InvalidApiMethodException($s);
        }
    }
}
