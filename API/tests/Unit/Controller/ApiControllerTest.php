<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiPath;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AccessTokenExpiredResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AccessTokenNotValidResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AuthenticationRequiredResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\MethodNotAllowedResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\MissingPermissionsResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\NotSecureResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Controller\ApiController;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for the ApiController
 */
final class ApiControllerTest extends TestCase
{
    private ?UserControllerInterface $ucMock;
    private ?AuthenticationControllerInterface $authMock;
    private ?RoutingControllerInterface $routingMock;
    private ?ApiRequestInterface $reqMock;
    private ?ApiControllerInterface $apiController;
    private ?ApiPath $path;

    public function setUp(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../../test.env");
        $dotenv->load();

        $this->ucMock = $this->createMock(UserControllerInterface::class);
        $this->authMock = $this->createMock(AuthenticationControllerInterface::class);
        $this->routingMock = $this->createMock(RoutingControllerInterface::class);
        $this->reqMock = $this->createMock(ApiRequestInterface::class);
        $this->apiController = new ApiController($this->routingMock, $this->authMock, ["user" => $this->ucMock]);

        $this->path = new ApiPath("abc");

        $this->reqMock
            ->method("getPath")
            ->willReturn($this->path);

        $this->reqMock
            ->method("getMethod")
            ->willReturn(ApiMethod::CONNECT);

        $this->ucMock
            ->method("deleteUser")
            ->with(0);
    }

    public function tearDown(): void
    {
        $this->ucMock = null;
        $this->authMock = null;
        $this->routingMock = null;
        $this->reqMock = null;
        $this->apiController  = null;
        $this->path = null;
    }

    /**
     * Test if the route method will be called with the correct request
     */
    public function testRoutingControllerGetsCorrectRequest(): void
    {
        //expects that routingController->route will be called with the correct request
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->with($this->path, ApiMethod::CONNECT)
            ->will($this->throwException(new Exception()));

        $this->expectException(Exception::class);

        $this->apiController->handleRequest($this->reqMock);
    }

    /**
     * Test if the method returns an ResourceNotFoundResponse if the routing throws a ApiPathNotFoundException
     */
    public function testApiPathNotExists(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->will($this->throwException(new ApiPathNotFoundException()));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, ResourceNotFoundResponse::class));
    }

    /**
     * Test if the method returns an MethodNotAllowedResponse if the routing throws a ApiMethodNotFoundException
     */
    public function testApiMethodNotExists(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->will($this->throwException(new ApiMethodNotFoundException("", ["test"])));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, MethodNotAllowedResponse::class));
        $this->assertEquals(["test"], json_decode($response->getData(), true)["availableMethods"]);
    }

    /**
     * Test if the routes function will be called correctly
     */
    public function testRouteFunctionCalledCorrectly(): void
    {
        //expects that routingController->route will be called with the correct request
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "ids" => [1, 2, 3],
                "function" => function ($req, $ids) {
                    if ($req->getMethod() === ApiMethod::CONNECT && $ids === [1, 2, 3]) {
                        return new InternalErrorResponse("test");
                    } else throw new Exception();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, InternalErrorResponse::class));
    }

    /**
     * Test if the routes function can access the controller array
     */
    public function testRouteFunctionCanAccessAdditionalControllers(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "ids" => [1, 2, 3],
                "function" => function ($req, $ids) {
                    if ($this->controller["user"]->deleteUser(0) === ["test"]) {
                        return new InternalErrorResponse("test");
                    } else throw new Exception();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, InternalErrorResponse::class));
    }

    /**
     * Test that the method returns an InternalErrorResponse (with database message) if the routes function throws an DbException
     */
    public function testRouteFunctionThrowsDbException(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "ids" => [1, 2, 3],
                "function" => function ($req, $ids) {
                    throw new DBException();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, InternalErrorResponse::class));
        $this->assertTrue(str_contains(json_decode($response->getData(), true)["msg"], "Database"));
    }

    /**
     * Test that the method returns an InternalErrorResponse (with default message) if the routes function throws an Exception
     */
    public function testRouteFunctionThrowsException(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "ids" => [1, 2, 3],
                "function" => function ($req, $ids) {
                    throw new Exception();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, InternalErrorResponse::class));
        $this->assertTrue(str_contains(json_decode($response->getData(), true)["msg"], "internal"));
    }

    /**
     * Test that the method returns the route function return value
     */
    public function testRouteFunctionReturnWillBeForwarded(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "ids" => [1, 2, 3],
                "function" => function ($req, $ids) {
                    return new NotSecureResponse();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, NotSecureResponse::class));
    }

    /**
     * Test that the method return a AuthenticationRequiredResponse if the authentication is required but no accessToken delivered
     */
    public function testAuthRequiredButNotThere(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => true
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn(null);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, AuthenticationRequiredResponse::class));
    }

    /**
     * Test that the method return a AccessTokenExpiredResponse if the accessToken is expired
     */
    public function testExpiredToken(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => true
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn("token");

        $this->authMock->expects($this->once())
            ->method("authenticateAccessToken")
            ->will($this->throwException(new ExpiredTokenException()));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, AccessTokenExpiredResponse::class));
    }

    /**
     * Test that the method return a AccessTokenNotValidResponse if the authenticator throws InvalidTokenException
     */
    public function testTokenNotValid1(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => true
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn("token");

        $this->authMock->expects($this->once())
            ->method("authenticateAccessToken")
            ->will($this->throwException(new InvalidTokenException()));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, AccessTokenNotValidResponse::class));
    }

    /**
     * Test that the method return a AccessTokenNotValidResponse if the authenticator throws InvalidArgumentException
     */
    public function testTokenNotValid2(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => true
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn("token");

        $this->authMock->expects($this->once())
            ->method("authenticateAccessToken")
            ->will($this->throwException(new InvalidArgumentException()));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, AccessTokenNotValidResponse::class));
    }

    /**
     * Test that the method return a MissingPermissionResponse if the authenticator->hasPermission returns false
     */
    public function testRequestNotPermitted(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => true,
                "permissions" => ["p"]
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn("token");

        $this->authMock->expects($this->once())
            ->method("authenticateAccessToken")
            ->with("token")
            ->willReturn(["auth"]);

        $this->authMock->expects($this->once())
            ->method("hasPermission")
            ->with(["requireAuth" => true, "permissions" => ["p"]], ["auth"])
            ->willReturn(false);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, MissingPermissionsResponse::class));
        $this->assertEquals(["p"], json_decode($response->getData(), true)["requiredPermissions"]);
    }

    /**
     * Test that the method return a MissingPermissionResponse if the authenticator->hasPermission returns false
     */
    public function testHandleRequestCompleteSuccessful(): void
    {
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => true,
                "permissions" => ["p"],
                "ids" => [1, 2, 3],
                "function" => function ($req, $ids) {
                    return new NotSecureResponse();
                }
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn("token");

        $this->authMock->expects($this->once())
            ->method("authenticateAccessToken")
            ->willReturn(["auth"]);

        $this->authMock->expects($this->once())
            ->method("hasPermission")
            ->willReturn(true);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, NotSecureResponse::class));
    }
}
