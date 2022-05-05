<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;

class AuthenticationController implements AuthenticationControllerInterface
{
    private UserControllerInterface $uc;
    private RefreshTokenAccessorInterface $refreshTokenAccessor;
    private RoleAccessorInterface $roleAccessor;

    public function __construct(UserControllerInterface $uc, RefreshTokenAccessorInterface $refreshTokenAccessor, RoleAccessorInterface $roleAccessor)
    {
        $this->uc = $uc;
        $this->refreshTokenAccessor = $refreshTokenAccessor;
        $this->roleAccessor = $roleAccessor;
    }

    public function authenticateAccessToken(string $accessToken): array
    {
    }


    public function getNewRefreshToken(int $userID): string
    {
    }

    public function getNewAccessToken(string $refreshToken): string
    {
    }


    public function hasPermission(array $route, array $givenPermissions): bool
    {
    }
}
