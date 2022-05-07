<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions;

use Throwable;

//Exception, that should be thrown if there is no route with the requested path, that use the requested method.
class ApiMethodNotFoundException extends RoutingException
{
    private array $availableMethods;

    public function __construct(string $message, array $availableMethods, $code = 0, Throwable $previous = null)
    {
        $this->availableMethods = $availableMethods;

        parent::__construct($message, $code, $previous);
    }

    public function getAvailableMethods(): array
    {
        return $this->availableMethods;
    }
}
