<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Integration\UserController;

use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;

/**
 * Integration Tests for the deleteUser method of UserController
 */
final class UciDeleteTest extends BaseUCITest
{

    /**
     * test if deletion works correctly
     */
    public function testDeleteUser()
    {
        $this->createUserWithEcr();

        $this->userController->deleteUser(1);

        $this->expectException(UserNotFoundException::class);
        $this->userController->deleteUser(1);
    }
}
