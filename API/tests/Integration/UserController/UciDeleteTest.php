<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;

/**
 * Integration Tests for the deleteUser method of UserController
 */
final class UciDeleteTest extends BaseUCITest
{

    /**
     * test if deletion throws exception if user not exists
     */
    public function testDeleteUserNotFound()
    {
        $this->expectException(UserNotFoundException::class);
        $this->userController->deleteUser(10);
    }

    /**
     * test if deletion works correctly
     */
    public function testDeleteFirstUser()
    {
        $this->createUserWithEcr();

        $this->userController->deleteUser(1);
    }
}
