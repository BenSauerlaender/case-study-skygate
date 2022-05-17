<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BadRequestResponses;

/**
 * Response that should be used if the requested user don't exists.
 */
class UserNotFoundResponse extends BadRequestResponse
{
    public function __construct()
    {
        parent::__construct("The user not exists.", 201);
    }
}
