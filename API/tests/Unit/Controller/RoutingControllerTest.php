<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\Interfaces\ApiPathInterface;
use BenSauer\CaseStudySkygateApi\Controller\RoutingController;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\BrokenRouteException;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for the RoutingController
 */
final class RoutingControllerTest extends TestCase
{

    private ?ApiPathInterface $pathMock;

    public function setUp(): void
    {
        $this->pathMock = $this->createMock(ApiPathInterface::class);
    }

    public function tearDown(): void
    {
        $this->pathMock = null;
    }

    /**
     * Tests if the routing throws the right exception if the route array is empty
     */
    public function testRouteWithoutRoutes(): void
    {
        $this->expectException(ApiPathNotFoundException::class);

        $rc = new RoutingController([]);
        $rc->route($this->pathMock, ApiMethod::GET);
    }

    /**
     * Tests if the routing throws the right exception if there is no route with specified path
     */
    public function testRouteAnUnavailablePath(): void
    {
        $this->expectException(ApiPathNotFoundException::class);

        $this->pathMock->expects($this->once())->willReturn(["test", "path"]);

        $rc = new RoutingController(["/test" => []]);
        $rc->route($this->pathMock, ApiMethod::GET);
    }

    /**
     * Tests if the routing throws the right exception if there is no route with specified path with params
     */
    public function testRouteAnUnavailablePathWithParams(): void
    {
        $this->expectException(ApiPathNotFoundException::class);

        $this->pathMock->expects($this->once())->willReturn(["test", "x"]);

        $rc = new RoutingController(["/test/{int}" => []]);
        $rc->route($this->pathMock, ApiMethod::GET);
    }

    /**
     * Tests if the routing throws the right exception if the routes array is broken
     * 
     * @dataProvider brokenRoutesProvider
     */
    public function testRouteWithBrokenRoutes(array $routes): void
    {
        $this->expectException(BrokenRouteException::class);

        $this->pathMock->expects($this->once())->willReturn(["test"]);

        $rc = new RoutingController($routes);
        $rc->route($this->pathMock, ApiMethod::GET);
    }

    public function brokenRoutesProvider(): array
    {
        return [
            "invalid Path" => [
                [0 => []]
            ],
            "no params" => [
                ["/test" => ["GET" => [
                    "requireAuth" => true,
                    "permissions" => ["user:read:these"],
                    "function" => function () {
                        return null;
                    }
                ]]]
            ],
            "no requireAuth" => [
                ["/test" => ["GET" => [
                    "params" => ["userID"],
                    "permissions" => ["user:read:these"],
                    "function" => function () {
                        return null;
                    }
                ]]]
            ],
            "no permission" => [
                ["/test" => ["GET" => [
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "function" => function () {
                        return null;
                    }
                ]]]
            ],
            "no function" => [
                ["/test" => ["GET" => [
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:read:these"]
                ]]]
            ],
            "invalid params" => [
                ["/test" => ["GET" => [
                    "params" => [333],
                    "requireAuth" => true,
                    "permissions" => ["user:read:these"],
                    "function" => function () {
                        return null;
                    }
                ]]]
            ]
        ];
    }

    /**
     * Tests if the routing returns the right array if no params are given.
     */
    public function testRouteReturnsCorrectArraySimple(array $routeIn, array $routeOut): void
    {

        $rc = new RoutingController(["/test" => ["GET" => [
            "params" => [],
            "requireAuth" => true,
            "permissions" => ["per1", "per2"],
            "function" => function () {
                return null;
            }
        ]]]);

        $this->pathMock->expects($this->once())->willReturn(["test"]);
        $out = $rc->route($this->pathMock, ApiMethod::GET);

        $this->assertEquals([
            "params" => [],
            "requireAuth" => true,
            "permissions" => ["per1", "per2"],
            "function" => function () {
                return null;
            }
        ], $out);
    }

    /**
     * Tests if the routing returns the right array if params are given.
     */
    public function testRouteReturnsCorrectArrayWithParams(): void
    {
        $rc = new RoutingController(["/test/{int}/{int}" => ["GET" => [
            "params" => ["para1", "para2"],
            "requireAuth" => false,
            "permissions" => [],
            "function" => function () {
                return null;
            }
        ]]]);

        $this->pathMock->expects($this->once())->willReturn(["test", "13", "0"]);
        $out = $rc->route($this->pathMock, ApiMethod::GET);

        $this->assertEquals([
            "params" => ["para1" => 13, "para2" => 0],
            "requireAuth" => false,
            "permissions" => [],
            "function" => function () {
                return null;
            }
        ], $out);
    }
}
