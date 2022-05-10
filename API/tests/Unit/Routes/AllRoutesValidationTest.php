<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Routes;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiPath;
use BenSauer\CaseStudySkygateApi\ApiComponents\Interfaces\ApiPathInterface;
use BenSauer\CaseStudySkygateApi\Routes;
use Closure;
use PHPUnit\Framework\TestCase;


/**
 * Tests to basic validate all routes
 */
final class AllRoutesValidationTest extends TestCase
{

    public function routesProvider(): array
    {
        $ret = [];
        $routes = Routes::getRoutes();
        foreach ($routes as $path => $methods) {
            foreach ($methods as $method => $route) {
                $ret["$path:$method"] = [$path, $method, $route];
            }
        }
        return $ret;
    }

    /**
     * Tests if the Path is valid.
     * 
     * @dataProvider routesProvider
     */
    public function testIsPathValid(string $path, string $method, array $route): void
    {
        $this->assertStringStartsWith("/", $path);
        $this->assertStringEndsNotWith("/", $path);
        $apiPath = new ApiPath(str_replace("{id}", "1", $path));
        $this->assertInstanceOf(ApiPathInterface::class, $apiPath, "Path can be parsed to ApiPath");
    }

    /**
     * Tests if the Method is valid.
     * 
     * @dataProvider routesProvider
     */
    public function testIsMethodValid(string $path, string $method, array $route): void
    {
        $apiMethod = ApiMethod::fromString($method);
        $this->assertInstanceOf(ApiMethod::class, $apiMethod, "Method can be parsed to ApiMethod");
    }

    /**
     * Tests if the Route has all keys
     * 
     * @dataProvider routesProvider
     */
    public function testRouteHasAllKeys(string $path, string $method, array $route): void
    {
        $this->assertArrayHasKey("ids", $route);
        $this->assertArrayHasKey("requireAuth", $route);
        $this->assertArrayHasKey("permissions", $route);
        $this->assertArrayHasKey("function", $route);
    }

    /**
     * Tests if the 'ids' array is valid
     * 
     * @dataProvider routesProvider
     */
    public function testIdsAreValid(string $path, string $method, array $route): void
    {
        foreach ($route["ids"] as $id) {
            $this->assertIsString($id);
            $this->assertEquals(1, preg_match("/^[a-zA-Z]+$/", $id));
        }

        //exact as many ids as {id} placeholders in path
        $this->assertEquals(sizeof($route["ids"]), substr_count($path, "{id}"));
    }

    /**
     * Tests if requireAuth is valid
     * 
     * @dataProvider routesProvider
     */
    public function testRequireAuthIsValid(string $path, string $method, array $route): void
    {
        $this->assertIsBool($route["requireAuth"]);
    }

    /**
     * Tests if the permissions array is valid
     * 
     * @dataProvider routesProvider
     */
    public function testPermissionsAreValid(string $path, string $method, array $route): void
    {
        if ($route["requireAuth"]) {
            $this->assertNotEmpty($route["permissions"]);
        } else {
            $this->assertEmpty($route["permissions"]);
        }

        foreach ($route["permissions"] as $perm) {
            $this->assertIsString($perm);
            $exp = explode(":", $perm);
            $this->assertEquals(3, sizeof($exp));
            foreach ($exp as $s) {
                $this->assertEquals(1, preg_match("/^[a-z]+$/", $s));
            }
        }
    }

    /**
     * Tests if the function is valid
     * 
     * @dataProvider routesProvider
     */
    public function testFunctionIsValid(string $path, string $method, array $route): void
    {
        $this->assertTrue(is_a($route["function"], Closure::class));
    }
}
