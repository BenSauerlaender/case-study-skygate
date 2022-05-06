<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;

class AuthenticationController implements AuthenticationControllerInterface
{
    private UserAccessorInterface $userAccessor;
    private RefreshTokenAccessorInterface $refreshTokenAccessor;
    private RoleAccessorInterface $roleAccessor;

    public function __construct(UserAccessorInterface $userAccessor, RefreshTokenAccessorInterface $refreshTokenAccessor, RoleAccessorInterface $roleAccessor)
    {
        $this->userAccessor = $userAccessor;
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
