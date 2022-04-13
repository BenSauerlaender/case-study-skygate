<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\SecurityUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use PHPUnit\Framework\TestCase;

/**
 * Base Testsuit for all UserController tests
 */
abstract class BaseUCTest extends TestCase
{

    protected ?SecurityUtilitiesInterface $securityUtilitiesMock;
    protected ?ValidatorInterface $validatorMock;
    protected ?UserAccessorInterface $userAccessorMock;
    protected ?RoleAccessorInterface $roleAccessorMock;
    protected ?EcrAccessorInterface $ecrAccessorMock;

    /**
     * The user controller to be tested
     *
     * @var ?UserControllerInterface
     */
    protected ?UserControllerInterface $userController;

    public function setUp(): void
    {

        //create all mocks
        $this->securityUtilitiesMock = $this->createMock(SecurityUtilities::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->userAccessorMock = $this->createMock(UserAccessorInterface::class);
        $this->roleAccessorMock = $this->createMock(RoleAccessorInterface::class);
        $this->ecrAccessorMock = $this->createMock(EcrAccessorInterface::class);

        //setUp the userController
        $this->userController = new UserController(
            $this->securityUtilitiesMock,
            $this->validatorMock,
            $this->userAccessorMock,
            $this->roleAccessorMock,
            $this->ecrAccessorMock,
        );
    }

    public function tearDown(): void
    {

        $this->securityUtilitiesMock = null;
        $this->validatorMock = null;
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
