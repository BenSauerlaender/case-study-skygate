<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Controller;

use Controller\PermissionController;
use Objects\ApiMethod;
use Objects\ApiPath;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for the PermissionController
 */
final class PermissionControllerTest extends TestCase
{
    /**
     * Tests if the isAllowed method returns correct value
     * 
     * @dataProvider PermissionProvider
     */
    public function testIsAllowedIfPermissionsIsEmpty(array $perms, string $path, string $method, array $userPerms, int $id, bool $return): void
    {
        $pc = new PermissionController($perms);
        $ret = $pc->isAllowed(new ApiPath($path), ApiMethod::fromString($method), $userPerms, $id);
        $this->assertEquals($return, $ret);
    }

    public function PermissionProvider(): array
    {

        return [
            "all empty" => [
                [],
                "path",
                "GET",
                [],
                1,
                false
            ],
            "permission for wrong path" => [
                ["another-path" => ["POST" => ["simplePerm" => function () {
                    return true;
                }]]],
                "path",
                "GET",
                ["simplePerm"],
                1,
                false
            ],
            "permission for wrong method" => [
                ["path" => ["POST" => ["simplePerm" => function () {
                    return true;
                }]]],
                "path",
                "GET",
                ["simplePerm"],
                1,
                false
            ],
            "wrong permission" => [
                ["path" => ["GET" => ["simplePerm" => function () {
                    return true;
                }]]],
                "path",
                "GET",
                ["wrongPerm"],
                1,
                false
            ],
            "correct permission" => [
                ["path" => ["GET" => ["simplePerm" => function () {
                    return true;
                }]]],
                "path",
                "GET",
                ["simplePerm"],
                1,
                true
            ],
            "correct permission 2" => [
                ["path" => ["GET" => ["simplePerm" => function () {
                    return true;
                }]]],
                "path",
                "GET",
                ["wrongPerm", "simplePerm", "wrongPerm2"],
                1,
                true
            ],
            "correct permission 3" => [
                ["path" => ["GET" => ["simplePerm1" => function () {
                    return true;
                }, "simplePerm" => function () {
                    return true;
                }, "simplePerm2" => function () {
                    return true;
                }]]],
                "path",
                "GET",
                ["wrongPerm", "simplePerm", "wrongPerm2"],
                1,
                true
            ],
            "correct permission 4" => [
                ["path" => ["GET" => ["simplePerm1" => function () {
                    return true;
                }, "simplePerm" => function () {
                    return true;
                }, "simplePerm2" => function () {
                    return true;
                }]]],
                "path",
                "GET",
                ["simplePerm1", "simplePerm", "simplePerm2"],
                1,
                true
            ],
            "correct permission wrong id" => [
                ["path/{x}" => ["GET" => ["simplePerm" => function (int $id, array $params) {
                    return $id === $params[0];
                }]]],
                "path/3",
                "GET",
                ["simplePerm"],
                1,
                false
            ],
            "correct permission correct id" => [
                ["path/{x}" => ["GET" => ["simplePerm" => function (int $id, array $params) {
                    return $id === $params[0];
                }]]],
                "path/3",
                "GET",
                ["simplePerm"],
                3,
                true
            ],
        ];
    }
}
