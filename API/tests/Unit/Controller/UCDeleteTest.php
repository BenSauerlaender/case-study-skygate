<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;

/**
 * Testsuit for UserController->delete method
 */
final class UCDeleteTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if there is no user with specified id
     */
    public function testDeleteNonExistingUser(): void
    {
        $this->userAccessorMock->expects($this->once())
            ->method("delete")
            ->with($this->equalTo("-1"))
            ->will($this->throwException(new UserNotFoundException()));

        $this->expectException(UserNotFoundException::class);

        $this->userController->deleteUser(-1);
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testDeleteUserSuccessful(): void
    {
        $id = 1;

        //expect userAcc->delete will be called with $id a param
        $this->userAccessorMock->expects($this->once())
            ->method("delete")
            ->with($this->equalTo($id));

        $this->ecrAccessorMock->expects($this->once())
            ->method("delete")
            ->with($this->equalTo($id))
            ->will($this->throwException(new EcrNotFoundException()));

        $this->userController->deleteUser($id);
    }
}
