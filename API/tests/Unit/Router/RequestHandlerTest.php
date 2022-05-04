<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Router\Response;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\BadRequestHandlerException;
use BenSauer\CaseStudySkygateApi\Router\RequestHandler;
use BenSauer\CaseStudySkygateApi\Router\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BaseResponse;
use PHPUnit\Framework\TestCase;

/**
 * Helper Class just for testing
 */
class SimpleResponse extends BaseResponse
{
    function __construct(int $code = 200)
    {
        $this->setCode($code);
    }
}

/**
 * Tests for the BaseResponse abstract class
 */
final class RequestHandlerTest extends TestCase
{

    /**
     * Tests if the Handler calls the Closure and pass the return correctly
     */
    public function testHandlerReturnCorrectly(): void
    {
        //Creates a new handle that just returns a Response
        $handler = new RequestHandler(function (RequestInterface $req) {
            return new SimpleResponse(404);
        });

        //creates an Mock request
        $request = $this->createMock(RequestInterface::class);

        //call the handle function
        $response = $handler->handle($request);

        //assert the right response
        $this->assertEquals(404, $response->getCode());
    }

    /**
     * Tests if the Handler has access to the request.
     */
    public function testHandlerCanAccessRequest(): void
    {
        //Creates a new handle that just gets  returns a Response
        $handler = new RequestHandler(function (RequestInterface $req) {
            $req->getQuery();
            return new SimpleResponse();
        });

        //creates an Mock request assert that getQuery is called once
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method("getQuery");

        //call the handle function
        $handler->handle($request);
    }

    /**
     * Tests if the Handler has access to the Controllers.
     */
    public function testHandlerCanAccessControllers(): void
    {
        //expects Controller will be accessed
        $controller = $this->createMock(UserControllerInterface::class);
        $controller->expects($this->once())->method("deleteUser");

        //Creates a new handle that just gets  returns a Response
        $handler = new RequestHandler(function (RequestInterface $req) {
            /** @var UserControllerInterface */
            $uc = $this->getController(UserControllerInterface::class);
            $uc->deleteUser(1);
            return new SimpleResponse();
        }, [UserControllerInterface::class => $controller]);

        //creates an Mock request assert that getQuery is called once
        $request = $this->createMock(RequestInterface::class);

        //call the handle function
        $handler->handle($request);
    }

    /**
     * Tests if the Handler throws BadRequestHandlerException on 'handle()' if a required controller is missing 
     */
    public function testHandlerThrowsExceptionIfControllerIsMissing(): void
    {
        //Creates a new handler that just uses a controller that not exists
        $handler = new RequestHandler(function (RequestInterface $req) {
            $this->getController("test");
        });

        //creates an Mock request
        $request = $this->createMock(RequestInterface::class);

        //expect exception during handle()
        $this->expectException(BadRequestHandlerException::class);

        //call the handle function
        $handler->handle($request);
    }

    /**
     * Tests if the Handler throws BadRequestHandlerException on construction if the controllers array is not coherent.
     */
    public function testHandlerThrowsExceptionIfControllerArrayBroken(): void
    {
        $controller = $this->createMock(UserControllerInterface::class);

        //expect Exception to be thrown during construction
        $this->expectException(BadRequestHandlerException::class);

        //Try to create a new handle with a wrong controllers array
        $handler = new RequestHandler(function (RequestInterface $req) {
        }, ["test" => $controller]);
    }
}
