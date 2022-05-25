<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Controller;

use Objects\ApiMethod;
use Objects\ApiPath;
use Controller\RoutingController;
use Exceptions\RoutingExceptions\ApiPathNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for the RoutingController
 */
final class RoutingControllerTest extends TestCase
{
    /**
     * Tests if the routing throws the right exception if the route array is empty
     */
    public function testRouteWithoutRoutes(): void
    {
        $this->expectException(ApiPathNotFoundException::class);

        $rc = new RoutingController([]);
        $rc->route(new ApiPath("test"), ApiMethod::GET);
    }

    /**
     * Tests if the routing throws the right exception if there is no route with specified path
     */
    public function testRouteAnUnavailablePath(): void
    {
        $this->expectException(ApiPathNotFoundException::class);

        $rc = new RoutingController(["/test" => []]);
        $rc->route(new ApiPath("test/path"), ApiMethod::GET);
    }

    /**
     * Tests if the routing throws the right exception if there is no route with specified path with params
     */
    public function testRouteAnUnavailablePathWithParams(): void
    {
        $this->expectException(ApiPathNotFoundException::class);

        $rc = new RoutingController(["/test/{x}" => []]);
        $rc->route(new ApiPath("test/x"), ApiMethod::GET);
    }

    /**
     * Tests if the routing returns the right array if no params are given.
     */
    public function testRouteReturnsCorrectArraySimple(): void
    {

        $rc = new RoutingController(["/test/path" => ["GET" => [
            "params" => [],
            "requireAuth" => true,
            "permissions" => ["per1", "per2"],
            "function" => function () {
                return null;
            }
        ]]]);

        $out = $rc->route(new ApiPath("test/path"), ApiMethod::GET);

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
        $rc = new RoutingController(["/test/{x}/{x}" => ["GET" => [
            "params" => ["param1", "param2"],
            "requireAuth" => false,
            "permissions" => [],
            "function" => function () {
                return null;
            }
        ]]]);

        $out = $rc->route(new ApiPath("test/13/0"), ApiMethod::GET);

        $this->assertEquals([
            "params" => ["param1" => 13, "param2" => 0],
            "requireAuth" => false,
            "permissions" => [],
            "function" => function () {
                return null;
            }
        ], $out);
    }
}
