<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\Exceptions\BadRequestHandlerException;
use BenSauer\CaseStudySkygateApi\Router\Interfaces\RequestHandlerInterface;

/**
 * Abstract class that is inherited by RequestHandler. The only purpose is to make controllers not visible to the internalHandler.
 */
abstract class RequestHandlerWithControllers implements RequestHandlerInterface
{

    /**
     * An Array of Controllers the handler requires.
     * 
     * Will be accessed from Closure.
     *
     * @var null|array<string,mixed> Array of ControllerName-ControllerInstance pairs.
     */
    private ?array $controllers;

    /**
     * Gets the specified controller object.
     *
     * @param  string $controllerName The name of the controller. Need to be the name of the Interface or Class.
     * @throws BadRequestHandlerException if the Controller don't exists.
     */
    protected function getController(string $controllerName): mixed
    {
        //if controller array is not set or the specified controller is not present throw an Exception
        if (is_null($this->controllers) or !isset($this->controllers[$controllerName])) {
            throw new BadRequestHandlerException("The RequestHandler is broken: No Controller: $controllerName found.");
        }
        //otherwise return the controller
        return $this->controllers[$controllerName];
    }

    /**
     * Sets the controllers array
     *
     * @param  array|null $controllers The controllers to set.
     * @throws BadRequestHandlerException if at least one name dont matches the type
     */
    protected function setControllers(?array $controllers): void
    {
        if (is_null($controllers)) {
            $this->controllers = null;
        } else {
            //Check for each pair that the name matches the type.
            foreach ($controllers as $name => $controller) {
                if (!is_a($controller, $name)) {
                    throw new BadRequestHandlerException("The Controller array is broken: The Controller with name $name is not such a controller.");
                }
            }
            //set the controllers array
            $this->controllers = $controllers;
        }
    }
}
