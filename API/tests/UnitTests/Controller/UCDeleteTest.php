<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for UserController->delete method
 */
final class UCDeleteTest extends TestCase
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testDeleteUserIDOutOfRange(): void
    {

        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );


        $this->expectException(OutOfRangeException::class);

        $uc->deleteUser(-1);
    }

    /**
     * Tests if the delete method calls the accessor correctly 
     */
    public function testDeleteUserSuccess(): void
    {
        $id = 1;

        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        //expect userAcc->delete will be called with $id a param
        $userAcc->expects($this->once())
            ->method("delete")
            ->with($this->equalTo($id));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $uc->deleteUser($id);
    }
}
