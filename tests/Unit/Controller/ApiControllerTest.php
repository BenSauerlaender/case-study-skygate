<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\Objects\ApiMethod;
use BenSauer\CaseStudySkygateApi\Objects\ApiPath;
use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\SuccessfulResponses\CreatedResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ServerErrorResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\MethodNotAllowedResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\MissingPermissionsResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Controller\ApiController;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidCookieException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidQueryException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\AuthorizationErrorResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\BadRequestResponse;
use Exception;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\MockObject\Rule\InvokedAtMostCount;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for the ApiController
 */
final class ApiControllerTest extends TestCase
{
    private ?UserControllerInterface $ucMock;
    private ?AuthenticationControllerInterface $authMock;
    private ?RoutingControllerInterface $routingMock;
    private ?RequestInterface $reqMock;
    private ?ApiControllerInterface $apiController;
    private ?ApiPath $path;

    public function setUp(): void
    {
        //load dotenv variables from 'test.env'
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, "../../test.env");
        $dotenv->load();

        $this->ucMock = $this->createMock(UserControllerInterface::class);
        $this->uaMock = $this->createMock(UserAccessorInterface::class);
        $this->authMock = $this->createMock(AuthenticationControllerInterface::class);
        $this->routingMock = $this->createMock(RoutingControllerInterface::class);
        $this->reqMock = $this->createMock(RequestInterface::class);
        $this->apiController = new ApiController($this->routingMock, $this->authMock, ["user" => $this->ucMock], ["user" => $this->uaMock]);

        $this->path = new ApiPath("abc/1");

        $this->reqMock
            ->expects($this->atMost(1))
            ->method("getPath")
            ->willReturn($this->path);

        $this->reqMock
            ->expects($this->atMost(1))
            ->method("getMethod")
            ->willReturn(ApiMethod::CONNECT);
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
        $this->assertEquals(["test"], json_decode($response->getJsonBody(), true)["availableMethods"]);
    }

    /**
     * Test if the routes function will be called correctly
     */
    public function testRouteFunctionCalledCorrectly(): void
    {
        $this->reqMock
            ->method("getBody")
            ->willReturn(["test"]);

        //expects that routingController->route will be called with the correct request
        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "params" => ["testID" => 1],
                "function" => function (RequestInterface $req, array $ids) {

                    if ($req->getBody() === ["test"] && $ids === ["testID" => 1]) {
                        return new CreatedResponse();
                    } else throw new Exception();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, CreatedResponse::class));
    }

    /**
     * Test if the routes function can access the controller array
     */
    public function testRouteFunctionCanAccessAdditionalControllers(): void
    {
        $this->ucMock
            ->expects($this->once())
            ->method("deleteUser")
            ->with(0);

        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "params" => ["testID"],
                "function" => function ($req, $ids) {
                    $this->controller["user"]->deleteUser(0);
                    return new CreatedResponse();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, CreatedResponse::class));
    }

    /**
     * Test if the routes function can access the accessor array
     */
    public function testRouteFunctionCanAccessAdditionalAccessors(): void
    {
        $this->uaMock
            ->expects($this->once())
            ->method("findByEmail")
            ->with("test")
            ->willReturn(1);

        $this->routingMock
            ->expects($this->once())
            ->method("route")
            ->willReturn([
                "requireAuth" => false,
                "params" => ["testID"],
                "function" => function ($req, $ids) {
                    if ($this->accessors["user"]->findByEmail("test") === 1) {
                        return new CreatedResponse();
                    } else throw new Exception();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, CreatedResponse::class));
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
                "params" => ["testID"],
                "function" => function ($req, $ids) {
                    throw new Exception();
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, InternalErrorResponse::class));
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
                "params" => ["testID"],
                "function" => function ($req, $ids) {
                    return new BadRequestResponse("", 0);
                }
            ]);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, BadRequestResponse::class));
    }

    /**
     * Test that the method return a AuthorizationRequiredResponse if the authentication is required but no accessToken delivered
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

        $this->assertTrue(is_a($response, AuthorizationErrorResponse::class));
        $this->assertStringContainsString('"errorCode":101', $response->getJsonBody());
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
            ->method("validateAccessToken")
            ->will($this->throwException(new ExpiredTokenException()));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, AuthorizationErrorResponse::class));
        $this->assertStringContainsString('"errorCode":103', $response->getJsonBody());
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
            ->method("validateAccessToken")
            ->will($this->throwException(new InvalidTokenException()));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, AuthorizationErrorResponse::class));
        $this->assertStringContainsString('"errorCode":102', $response->getJsonBody());
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
            ->method("validateAccessToken")
            ->will($this->throwException(new InvalidArgumentException()));

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, AuthorizationErrorResponse::class));
        $this->assertStringContainsString('"errorCode":102', $response->getJsonBody());
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
                "permissions" => ["required"]
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn("token");

        $this->authMock->expects($this->once())
            ->method("validateAccessToken")
            ->with("token")
            ->willReturn(["permissions" => ["given"]]);

        $this->authMock->expects($this->once())
            ->method("hasPermissions")
            ->with(["given"], ["required"])
            ->willReturn(false);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, MissingPermissionsResponse::class));
        $this->assertEquals(["required"], json_decode($response->getJsonBody(), true)["requiredPermissions"]);
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
                "params" => [1, 2, 3],
                "function" => function ($req, $ids) {
                    return new BadRequestResponse("", 0);
                }
            ]);

        $this->reqMock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn("token");

        $this->authMock->expects($this->once())
            ->method("validateAccessToken")
            ->willReturn(["permissions" => ["p"]]);

        $this->authMock->expects($this->once())
            ->method("hasPermissions")
            ->willReturn(true);

        $response = $this->apiController->handleRequest($this->reqMock);

        $this->assertTrue(is_a($response, BadRequestResponse::class));
    }

    /**
     * Tests if the method throws an exception if the connection is in production not secure.
     */
    public function testFetchRequestNoSslInProduction(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        unset($SERVER["HTTPS"]);
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(NotSecureException::class);

        $this->apiController->fetchRequest($SERVER, [], "");
    }

    /**
     * Test if the method throws an exception if the path is does not start with the prefix
     */
    public function testFetchRequestPathDontStartWithPrefix(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = "test";
        $SERVER["REQUEST_URI"] = "/path/to/x";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidPathException::class);

        $this->apiController->fetchRequest($SERVER, [], "pre");
    }

    /**
     * Test if the method throws an exception if the path is not valid
     */
    public function testFetchRequestPathIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/x.txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidPathException::class);

        $this->apiController->fetchRequest($SERVER, [], "/path/to");
    }

    /**
     * Test if the method throws an exception if the method is not valid
     */
    public function testFetchRequestMethodIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "SEARCH";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidMethodException::class);

        $this->apiController->fetchRequest($SERVER, [], "/path/to");
    }

    /**
     * Test if the method throws an exception if the query is not valid
     */
    public function testFetchRequestQueryIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "123";

        $this->expectException(InvalidQueryException::class);

        $this->apiController->fetchRequest($SERVER, [], "/path/to");
    }

    /**
     * Test if the method throws an exception if a header is not valid
     */
    public function testFetchRequestHeaderIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidArgumentException::class);

        $this->apiController->fetchRequest($SERVER, [1 => "test"], "/path/to");
    }

    /**
     * Test if the method throws an exception if a cookie is not valid
     */
    public function testFetchRequestCookieIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidCookieException::class);

        $this->apiController->fetchRequest($SERVER, ["t1" => "test", "Cookie" => "1 2 3 "], "/path/to");
    }

    /**
     * Test if the method throws an exception if the body cant be decoded.
     */
    public function testFetchRequestBodyIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "POST";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(JsonException::class);

        $this->apiController->fetchRequest($SERVER, [], "/path/to", "/////");
    }

    /**
     * Test if the body is null if the Method is not PUT or POST.
     */
    public function testFetchRequestBodyOnGet(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $req = $this->apiController->fetchRequest($SERVER, [], "/path/to", '{ "test" : 123}');
        $this->assertNull($req->getBody());
    }

    /**
     * Test if the body is null if the body string is empty.
     */
    public function testFetchRequestBodyIsEmpty(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "POST";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $req = $this->apiController->fetchRequest($SERVER, [], "/path/to", "");
        $this->assertNull($req->getBody());
    }

    /**
     * Test if the method works as expected
     */
    public function testFetchRequestSuccessful(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt?123";
        $SERVER["REQUEST_METHOD"] = "POST";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $req = $this->apiController->fetchRequest($SERVER, ["t1" => "test", "Cookie" => "c=cookie123"], "/path/to", '{ "test" : 123}');

        $this->assertEquals("/txt", $req->getPath());
        $this->assertEquals(ApiMethod::POST, $req->getMethod());
        $this->assertEquals(3, $req->getQueryValue("search"));
        $this->assertEquals("test", $req->getQueryValue("p"));
        $this->assertEquals("test", $req->getHeader("t1"));
        $this->assertEquals("cookie123", $req->getCookie("c"));
        $this->assertEquals(123, $req->getBody()["test"]);
    }
}
