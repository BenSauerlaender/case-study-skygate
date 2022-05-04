<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\Router\Interfaces\ApiRequestInterface;

/**
 * Controller that handles authentication stuff.
 */
interface AuthenticationControllerInterface
{
    public function authenticate(ApiRequestInterface $req): void;
    public function checkPermission(array $requiredPermissions, array $permissions): void;
}
