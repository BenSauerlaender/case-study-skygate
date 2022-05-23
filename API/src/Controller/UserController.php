<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\Objects\Responses\BadRequestResponses\UserNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateUserException;
use BenSauer\CaseStudySkygateApi\Exceptions\ShouldNeverHappenException;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\SecurityControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ValidationControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ArrayIsEmptyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ValidationException;

use function BenSauer\CaseStudySkygateApi\Utilities\mapped_implode;
use function PHPUnit\Framework\isNull;

class UserController implements UserControllerInterface
{
    private SecurityControllerInterface $securityUtil;
    private ValidationControllerInterface $ValidationController;
    private UserAccessorInterface $userAccessor;
    private RoleAccessorInterface $roleAccessor;
    private EcrAccessorInterface $ecrAccessor;

    //simple constructor to set all properties //should only be used by UserInterface
    public function __construct(SecurityControllerInterface $securityUtil, ValidationControllerInterface $ValidationController, UserAccessorInterface $userAccessor, RoleAccessorInterface $roleAccessor, EcrAccessorInterface $ecrAccessor)
    {
        $this->securityUtil = $securityUtil;
        $this->ValidationController = $ValidationController;
        $this->userAccessor = $userAccessor;
        $this->roleAccessor = $roleAccessor;
        $this->ecrAccessor = $ecrAccessor;
    }


    public function createUser(array $fields): array
    {
        //checks if all required fields exists
        $missingFields = array_diff_key(["email" => "email", "name" => "name", "postcode" => "postcode", "city" => "city", "phone" => "phone", "password" => "password"], $fields);
        if (sizeOf($missingFields) !== 0) {
            throw new RequiredFieldException($missingFields);
        }

        //validate all fields (except "role").
        $valid = $this->ValidationController->validate(\array_diff_key($fields, ["role" => ""]));

        if ($valid !== true) {
            $reasons = $valid;
            throw new InvalidPropertyException($reasons);
        }

        //check if the email is free
        if (!$this->isEmailFree($fields["email"])) {
            throw new InvalidPropertyException(["email" => ["IS_TAKEN"]]);
        }

        //get the role id. Default role is "user"
        $roleName = $fields["role"] ?? "user";
        $roleID = $this->getRoleID($roleName);

        //hash the password
        $hashedPassword = $this->securityUtil->hashPassword($fields["password"]);

        //generate a 10-char verification code
        $verificationCode = $this->securityUtil->generateCode(10);

        //insert the new user into the database
        try {
            $this->userAccessor->insert(
                $fields["email"],
                $fields["name"],
                $fields["postcode"],
                $fields["city"],
                $fields["phone"],
                $hashedPassword,
                false,
                $verificationCode,
                $roleID
            );
        } catch (RoleNotFoundException | DuplicateEmailException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("Email and Role were checked before", 0, $e); // @codeCoverageIgnore
        }

        //find the just created user in the database and return his id.
        $id = $this->userAccessor->findByEmail($fields["email"]);
        if (is_null($id)) throw new ShouldNeverHappenException("The just created user(email: " . $fields["email"] . ") can't be found in the database."); // @codeCoverageIgnore
        return array("id" => $id, "verificationCode" => $verificationCode);
    }

    public function getUser(int $id): array
    {
        try {
            $user = $this->userAccessor->get($id);

            $role = $this->roleAccessor->get($user["roleID"]);

            return [
                "email" => $user["email"],
                "name" => $user["name"],
                "postcode" => $user["postcode"],
                "city" => $user["city"],
                "phone" => $user["phone"],
                "role" => $role["name"],
            ];
        } catch (RoleNotFoundException $e) {
            throw new ShouldNeverHappenException("Because of the db relations.", 0, $e);
        }
    }

    public function deleteUser(int $id): void
    {
        //delete emailChangeRequest if there is one
        try {
            $this->ecrAccessor->deleteByUserID($id);
        } catch (EcrNotFoundException $e) {
        } //no need to do something. its fine

        $this->userAccessor->delete($id);
    }

    public function updateUser(int $id, array $fields): void
    {
        if (array_key_exists("password", $fields)) throw new InvalidPropertyException(["password" => ["UNSUPPORTED"]]);
        if (array_key_exists("email", $fields)) throw new InvalidPropertyException(["email" => ["UNSUPPORTED"]]);

        if (sizeof($fields) === 0) throw new ArrayIsEmptyException();

        //validate all fields (except "role").
        $valid = $this->ValidationController->validate(\array_diff_key($fields, ["role" => ""]));
        if ($valid !== true) {
            $reasons = $valid;
            throw new InvalidPropertyException($reasons);
        }

        //replace role name by its id
        if (array_key_exists("role", $fields)) {
            $fields["roleID"] = $this->getRoleID($fields["role"]);
            unset($fields["role"]);
        }

        //update the database
        try {
            $this->userAccessor->update($id, $fields);
        } catch (ValidationException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("userAccessor->update throws an exception, even though all perquisites are checked", 0, $e); // @codeCoverageIgnore
        }
    }

    public function verifyUser(int $id, string $verificationCode): bool
    {
        //get the users fields
        $user = $this->userAccessor->get($id);

        //check if the user is verified already
        if ($user["verified"]) throw new BadMethodCallException("The User (with id: " . $id . ") is already verified");

        //check if the verification code is correct
        if ($user["verificationCode"] !== $verificationCode) return false;

        //update the database
        try {
            $this->userAccessor->update($id, array("verificationCode" => null, "verified" => true));
        } catch (UserNotFoundException | ValidationException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("userAccessor->update throws an exception, even though all perquisites are checked. $e", 0, $e); // @codeCoverageIgnore
        }

        //everything went well
        return true;
    }

    public function checkEmailPassword(string $email, string $password): bool
    {
        $id = $this->userAccessor->findByEmail($email);
        if (is_null($id)) {
            throw new UserNotFoundException();
        }

        try {
            $user = $this->userAccessor->get($id);
        } catch (UserNotFoundException $e) {
            throw new ShouldNeverHappenException("The user was just found by email", 0, $e);
        }

        return $this->securityUtil->checkPassword($password, $user["hashedPass"]);
    }

    public function updateUsersPassword(int $id, string $newPassword, string $oldPassword): bool
    {
        //get the users fields
        $user = $this->userAccessor->get($id);

        //check if old password is correct
        if (!$this->securityUtil->checkPassword($oldPassword, $user["hashedPass"])) return false;

        //validate new password
        try {
            $valid = $this->ValidationController->validate(["password" => $newPassword]);
            if ($valid !== true) {
                $reasons = $valid;
                throw new InvalidPropertyException($reasons);
            }
        } catch (ArrayIsEmptyException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("Array is not empty.", 0, $e); // @codeCoverageIgnore
        }

        //update the database
        try {
            $this->userAccessor->update($id, array("hashedPass" => $this->securityUtil->hashPassword($newPassword)));
        } catch (UserNotFoundException | ValidationException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("userAccessor->update throws an exception, even though all perquisites are checked", 0, $e); // @codeCoverageIgnore
        }

        return true;
    }

    public function requestUsersEmailChange(int $id, string $newEmail): string
    {
        //validate new email
        try {
            $valid = $this->ValidationController->validate(["email" => $newEmail]);
            if ($valid !== true) {
                $reasons = $valid;
                throw new InvalidPropertyException($reasons);
            }
        } catch (ArrayIsEmptyException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("Array is not empty.", 0, $e); // @codeCoverageIgnore
        }

        //check if the email is free
        if (!$this->isEmailFree($newEmail)) {
            throw new InvalidPropertyException(["email" => ["IS_TAKEN"]]);
        }

        //delete old Request if there is one
        try {
            $this->ecrAccessor->deleteByUserID($id);
        } catch (EcrNotFoundException $e) {
        } //no need to do something. its fine

        //generate a 10-char verification code
        $verificationCode = $this->securityUtil->generateCode(10);

        //insert the Request to the database
        try {
            $this->ecrAccessor->insert($id, $newEmail, $verificationCode);
        } catch (DuplicateUserException | DuplicateEmailException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("All Request from this user should be deleted and the email is checked", 0, $e); // @codeCoverageIgnore
        }

        //return the verification code
        return $verificationCode;
    }

    public function verifyUsersEmailChange(int $id, string $code): bool
    {
        //get the RequestID
        $RequestID = $this->ecrAccessor->findByUserID($id);
        if (is_null($RequestID)) throw new EcrNotFoundException("emailChangeRequest from UserID: $id");

        try {
            $Request = $this->ecrAccessor->get($RequestID);


            //check if the verification code is correct
            if ($Request["verificationCode"] !== $code) return false;

            //update the user
            try {
                $this->userAccessor->update($id, ["email" => $Request["newEmail"]]);
            } catch (ValidationException $e) { // @codeCoverageIgnore
                throw new ShouldNeverHappenException("field array is valid", 0, $e); // @codeCoverageIgnore
            }

            //remove the Request
            $this->ecrAccessor->delete($RequestID);
        } catch (EcrNotFoundException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("The just found Request with id: $RequestID can now not be found anymore.", 0, $e); // @codeCoverageIgnore
        }
        return true;
    }

    /**
     * Gets the id of a specified role name
     *
     * @param  string $name The roles name.
     * @return int          returns the roles id.
     * 
     * @throws DBException if there is a problem with the database.
     *          (RoleNotFoundException | ...)
     */
    private function getRoleID(string $name): int
    {
        $roleID = $this->roleAccessor->findByName($name);
        if (is_null($roleID)) throw new RoleNotFoundException("The role '" . $name . " is not a valid role", 1);
        return $roleID;
    }


    /**
     * Checks if the specified email if free to use
     * 
     * Checks if the email is used by a user.
     * Checks if the email is Requested by a user.
     *
     * @param  string $email    The email to check for.
     * @return bool             returns true if the email is free, otherwise false.
     * 
     * @throws DBexception        if there is a problem with the database.
     */
    private function isEmailFree(string $email): bool
    {
        if (!is_null(($this->userAccessor->findByEmail($email)))) return false;
        if (!is_null(($this->ecrAccessor->findByEmail($email)))) return false;
        return true;
    }
}
