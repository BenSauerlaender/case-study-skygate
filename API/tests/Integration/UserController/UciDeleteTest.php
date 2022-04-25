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
