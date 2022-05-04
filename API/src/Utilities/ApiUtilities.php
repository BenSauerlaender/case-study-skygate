<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ResponseInterface;

class ApiUtilities
{
    /**
     * Utility function to send a response to the user
     *
     * @param  ResponseInterface $response The response to be send
     * @param  string $domain The Servers Domain.
     */
    static public function sendResponse(ResponseInterface $response, string $domain, string $basePath): void
    {
        //clear all headers
        header_remove();

        //set response code
        http_response_code($response->getCode());

        //set all custom headers
        foreach ($response->getHeaders() as $key => $value) {
            header("$key: $value");
        }

        //set all cookies
        foreach ($response->getCookies() as $cookie) {
            $cookieInfo = $cookie->get();
            setcookie(
                $cookieInfo["name"],
                $cookieInfo["value"],
                ($cookieInfo["expiresIn"] <= 0) ? 0 : ($cookieInfo["expiresIn"] + time()),
                $basePath . $cookieInfo["path"],
                $domain,
                $cookieInfo["secure"],
                $cookieInfo["httpOnly"]
            );
        }

        //set data if provided
        $data = $response->getData();
        if ($data !== "") {
            echo $data;
        }
    }
}
