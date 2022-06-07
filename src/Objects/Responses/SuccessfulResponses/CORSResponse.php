<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses\SuccessfulResponses;

use Objects\Interfaces\RequestInterface;

/**
 * Response that should be used if the request was an OPTIONS CORS prelight and successful
 */
class CORSResponse extends NoContentResponse
{
    public function __construct(RequestInterface $request)
    {
        $this->addHeader("Access-Control-Allow-Headers", $request->getHeader("Access-Control-Request-Headers"));
        $this->addHeader("Access-Control-Allow-Methods", $request->getHeader("Access-Control-Request-Method"));
        parent::__construct();
    }
}
