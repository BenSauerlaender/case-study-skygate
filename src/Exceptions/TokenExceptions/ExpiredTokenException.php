<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions;

/** 
 * Exception, that should be thrown if a token is expired. 
 */
class ExpiredTokenException extends InvalidTokenException
{
}
