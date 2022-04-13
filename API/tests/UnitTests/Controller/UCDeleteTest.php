<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller;

use OutOfRangeException;

/**
 * Testsuit for UserController->delete method
 */
final class UCDeleteTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testDeleteUserIDOutOfRange(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("is not a valid id");

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

        $this->userController->deleteUser($id);
    }
}
