<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller;

use Objects\ApiMethod;
use Objects\Interfaces\ApiPathInterface;
use Controller\Interfaces\PermissionControllerInterface;
use Exception;

/**
 * Implementation of PermissionControllerInterface
 */
class PermissionController implements PermissionControllerInterface
{

    /**
     * An array of all available permissions
     *
     * @var array<string,array<string,array<string,Closure>>>    $permissionsMap = [
     *      <route_path> => [
     *          <route_method> => [
     *              <permission> => (Closure) The function(closure) to determine if the user is allowed to execute the specific route
     *          ]
     *      ]
     * ]
     */
    private array $permissionsMap;

    /**
     * @param array<string,array<string,array<string,Closure>>>    $permissionsMap = [
     *      <route_path> => [
     *          <route_method> => [
     *              <permission> => (Closure) The function(closure) to determine if the user is allowed to execute the specific route
     *          ]
     *      ]
     * ]
     */
    public function __construct(array $permissionsMap)
    {
        $this->permissionsMap = $permissionsMap;
    }

    public function isAllowed(ApiPathInterface $path, ApiMethod $method, array $permissions, int $requesterID): bool
    {
        //get path and method as there string representations
        $pathString = $path->getStringWithPlaceholders();
        $methodString = $method->toString();

        //check if path is available; get available methods if so
        if (!array_key_exists($pathString, $this->permissionsMap)) return false;
        $availableMethods = $this->permissionsMap[$pathString];

        //check if method is available; get available permissions if so
        if (!array_key_exists($methodString, $availableMethods)) return false;
        $availablePermissions = $availableMethods[$methodString];

        //go through all available permissions
        foreach ($availablePermissions as $perm => $func) {
            if (!in_array($perm, $permissions)) continue;

            //the requester is allowed if at least one of his permissions are available for the requested route and the permission-function returns true
            if ($func($requesterID, $path->getParameters())) return true;
        }

        //if no permission function passes: the requester is not allowed
        return false;
    }
}
