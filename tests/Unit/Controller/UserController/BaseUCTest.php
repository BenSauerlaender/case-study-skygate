<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Controller\UserController;

use Controller\Interfaces\UserControllerInterface;
use Controller\UserController;
use DbAccessors\Interfaces\EcrAccessorInterface;
use DbAccessors\Interfaces\RoleAccessorInterface;
use DbAccessors\Interfaces\UserAccessorInterface;
use DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use Controller\Interfaces\SecurityControllerInterface;
use Controller\Interfaces\ValidationControllerInterface;
use Controller\SecurityController;
use PHPUnit\Framework\TestCase;

/**
 * Base Test suite for all UserController tests
 */
abstract class BaseUCTest extends TestCase
{

    protected ?SecurityControllerInterface $SecurityControllerMock;
    protected ?ValidationControllerInterface $ValidationControllerMock;
    protected ?UserAccessorInterface $userAccessorMock;
    protected ?RoleAccessorInterface $roleAccessorMock;
    protected ?EcrAccessorInterface $ecrAccessorMock;
    protected ?RefreshTokenAccessorInterface $rtAccessorMock;

    /**
     * The user controller to be tested
     *
     * @var ?UserControllerInterface
     */
    protected ?UserControllerInterface $userController;

    public function setUp(): void
    {

        //create all mocks
        $this->SecurityControllerMock = $this->createMock(SecurityController::class);
        $this->ValidationControllerMock = $this->createMock(ValidationControllerInterface::class);
        $this->userAccessorMock = $this->createMock(UserAccessorInterface::class);
        $this->roleAccessorMock = $this->createMock(RoleAccessorInterface::class);
        $this->ecrAccessorMock = $this->createMock(EcrAccessorInterface::class);
        $this->rtAccessorMock = $this->createMock(RefreshTokenAccessorInterface::class);

        //setUp the userController
        $this->userController = new UserController(
            $this->SecurityControllerMock,
            $this->ValidationControllerMock,
            $this->userAccessorMock,
            $this->roleAccessorMock,
            $this->ecrAccessorMock,
            $this->rtAccessorMock,
        );
    }

    public function tearDown(): void
    {

        $this->SecurityControllerMock = null;
        $this->ValidationControllerMock = null;
        $this->userAccessorMock = null;
        $this->roleAccessorMock = null;
        $this->ecrAccessorMock = null;

        $this->userController = null;
    }

    /**
     * Configure the userAccessorMock and ecrAccessorMock so that, when passed in a NAND combination, the email isn't free in at least one of both accessors 
     * 
     * Should be used in combination with the NANDProvider
     *
     * @param  bool $user True if userAccessor should mimic to not have the email in the table. 
     * @param  bool $ecr True if ecrAccessor should mimic to not have the email in the table. 
     */
    protected function configEmailAvailability(bool $freeInUser, bool $freeInEcr)
    {

        if ($freeInUser) {
            //email in use
            $this->userAccessorMock->method("findByEmail")->willReturn(null);
        } else {
            //email not found
            $this->userAccessorMock->method("findByEmail")->willReturn(0);
        }

        if ($freeInEcr) {
            //email in use
            $this->ecrAccessorMock->method("findByEmail")->willReturn(null);
        } else {
            //email not found
            $this->ecrAccessorMock->method("findByEmail")->willReturn(0);
        }
    }
}
