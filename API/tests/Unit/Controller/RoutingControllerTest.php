<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiPath;
use BenSauer\CaseStudySkygateApi\Controller\RoutingController;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for the RoutingController
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
     * Tests if the routing throws the right exception if there is no route with specified path with ids
     */
    public function testRouteAnUnavailablePathWithIDs(): void
    {
        $this->expectException(ApiPathNotFoundException::class);

        $rc = new RoutingController(["/test/{id}" => []]);
        $rc->route(new ApiPath("test/x"), ApiMethod::GET);
    }

    /**
     * Tests if the routing returns the right array if no ids are given.
     */
    public function testRouteReturnsCorrectArraySimple(): void
    {

        $rc = new RoutingController(["/test/path" => ["GET" => [
            "ids" => [],
            "requireAuth" => true,
            "permissions" => ["per1", "per2"],
            "function" => function () {
                return null;
            }
        ]]]);

        $out = $rc->route(new ApiPath("test/path"), ApiMethod::GET);

        $this->assertEquals([
            "ids" => [],
            "requireAuth" => true,
            "permissions" => ["per1", "per2"],
            "function" => function () {
                return null;
            }
        ], $out);
    }

    /**
     * Tests if the routing returns the right array if ids are given.
     */
    public function testRouteReturnsCorrectArrayWithIds(): void
    {
        $rc = new RoutingController(["/test/{id}/{id}" => ["GET" => [
            "ids" => ["id1", "id2"],
            "requireAuth" => false,
            "permissions" => [],
            "function" => function () {
                return null;
            }
        ]]]);

        $out = $rc->route(new ApiPath("test/13/0"), ApiMethod::GET);

        $this->assertEquals([
            "ids" => ["id1" => 13, "id2" => 0],
            "requireAuth" => false,
            "permissions" => [],
            "function" => function () {
                return null;
            }
        ], $out);
    }
}
