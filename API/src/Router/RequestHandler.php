<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router;

use BenSauer\CaseStudySkygateApi\Router\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Router\Responses\Interfaces\ResponseInterface;
use Closure;

/**
 * Class that can be constructed into specific handlers, which than can handle specific Requests
 */
class RequestHandler extends RequestHandlerWithControllers
{
    /**
     * The 'real' handler function that 'handle' wraps around
     *
     * @var Closure The Function to handle a specific request.
     */
    private Closure $internalHandler;

    /**
     * Creates a new request handler
     *
     * @param  Closure    $internalHandler  The internal handle function.
     * @param  array|null $controllers  The Controllers that can be used.
     * @throws BadRequestHandlerException if the controllers array is invalid.
     */
    public function __construct(Closure $internalHandler, ?array $controllers = null)
    {
        $this->internalHandler = $internalHandler;
        $this->setControllers($controllers);
    }


    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->internalHandler->call($this, $request);
    }
}
