<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller;

use BadMethodCallException;
use Controller\Interfaces\UserControllerInterface;
use DbAccessors\Interfaces\RoleAccessorInterface;
use DbAccessors\Interfaces\UserAccessorInterface;
use DbAccessors\Interfaces\EcrAccessorInterface;
use Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateUserException;
use Exceptions\ShouldNeverHappenException;
use Controller\Interfaces\SecurityControllerInterface;
use Controller\Interfaces\ValidationControllerInterface;
use DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use Exceptions\DBExceptions\DBException;
use Exceptions\ValidationExceptions\ArrayIsEmptyException;
use Exceptions\ValidationExceptions\InvalidPropertyException;
use Exceptions\ValidationExceptions\MissingPropertiesException;
use Exceptions\ValidationExceptions\ValidationException;
use Objects\Cookies\RefreshTokenCookie;

/**
 * Implementation of SecurityControllerInterface
 */
class UserController implements UserControllerInterface
{
    private SecurityControllerInterface $securityController;
    private ValidationControllerInterface $ValidationController;
    private UserAccessorInterface $userAccessor;
    private RoleAccessorInterface $roleAccessor;
    private EcrAccessorInterface $ecrAccessor;
    private RefreshTokenAccessorInterface $rtaAccessor;

    public function __construct(SecurityControllerInterface $securityController, ValidationControllerInterface $ValidationController, UserAccessorInterface $userAccessor, RoleAccessorInterface $roleAccessor, EcrAccessorInterface $ecrAccessor, RefreshTokenAccessorInterface $rtaAccessor)
    {
        $this->securityController = $securityController;
        $this->ValidationController = $ValidationController;
        $this->userAccessor = $userAccessor;
        $this->roleAccessor = $roleAccessor;
        $this->ecrAccessor = $ecrAccessor;
        $this->rtaAccessor = $rtaAccessor;
    }


    public function createUser(array $properties): array
    {
        //checks if all required properties exists
        $missingProperties =  array_diff_key(array_flip(["email", "name", "postcode", "city", "phone", "password"]), $properties);
        if (sizeOf($missingProperties) !== 0) {
            throw new MissingPropertiesException($missingProperties);
        }

        //validate all properties (except "role").
        $valid = $this->ValidationController->validate(\array_diff_key($properties, ["role" => ""]));

        //if validation fails
        if ($valid !== true) {
            $reasons = $valid;
            throw new InvalidPropertyException($reasons);
        }

        //check if the email is free
        if (!$this->isEmailFree($properties["email"])) {
            throw new InvalidPropertyException(["email" => ["IS_TAKEN"]]);
        }

        //get the role id. Default role is "user"
        $roleName = $properties["role"] ?? "user";
        $roleID = $this->getRoleID($roleName);

        //hash the password
        $hashedPassword = $this->securityController->hashPassword($properties["password"]);

        //generate a 10-char verification code
        $verificationCode = $this->securityController->generateCode(10);

        //insert the new user into the database
        try {
            $this->userAccessor->insert(
                $properties["email"],
                $properties["name"],
                $properties["postcode"],
                $properties["city"],
                $properties["phone"],
                $hashedPassword,
                false,
                $verificationCode,
                $roleID
            );
        } catch (RoleNotFoundException | DuplicateEmailException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("email and Role were checked before", $e); // @codeCoverageIgnore
        }

        //find the just created user in the database and return his id.
        $id = $this->userAccessor->findByEmail($properties["email"]);
        if (is_null($id)) throw new ShouldNeverHappenException("the user was just created."); // @codeCoverageIgnore

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
            throw new ShouldNeverHappenException("the roleID was just found in the user table.", $e);
        }
    }

    public function deleteUser(int $id): void
    {
        //delete emailChangeRequest if there is one
        try {
            $this->rtaAccessor->deleteByUserID($id);
            $this->ecrAccessor->deleteByUserID($id);
        } catch (EcrNotFoundException $e) {
        } //no need to do something. its fine

        $this->userAccessor->delete($id);
    }

    public function updateUser(int $id, array $properties): void
    {
        if (array_key_exists("password", $properties)) throw new InvalidPropertyException(["password" => ["UNSUPPORTED"]]);
        if (array_key_exists("email", $properties)) throw new InvalidPropertyException(["email" => ["UNSUPPORTED"]]);

        if (sizeof($properties) === 0) throw new ArrayIsEmptyException();

        //validate all properties (except "role").
        $valid = $this->ValidationController->validate(\array_diff_key($properties, ["role" => ""]));
        //if validation fails
        if ($valid !== true) {
            $reasons = $valid;
            throw new InvalidPropertyException($reasons);
        }

        //replace role name by its id
        if (array_key_exists("role", $properties)) {
            $properties["roleID"] = $this->getRoleID($properties["role"]);
            unset($properties["role"]);
        }

        //update the database
        try {
            $this->userAccessor->update($id, $properties);
        } catch (ValidationException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("all properties were validated before", $e); // @codeCoverageIgnore
        }
    }

    public function verifyUser(int $id, string $verificationCode): bool
    {
        //get the users properties
        $user = $this->userAccessor->get($id);

        //check if the user is verified already
        if ($user["verified"]) throw new BadMethodCallException("The User (with id: " . $id . ") is already verified");

        //check if the verification code is correct
        if ($user["verificationCode"] !== $verificationCode) return false;

        //update the database
        try {
            $this->userAccessor->update($id, array("verificationCode" => null, "verified" => true));
        } catch (UserNotFoundException | ValidationException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("all properties were validated before", $e); // @codeCoverageIgnore
        }

        //everything went well
        return true;
    }

    public function checkEmailPassword(string $email, string $password): bool
    {
        //get userID
        $id = $this->userAccessor->findByEmail($email);
        if (is_null($id)) {
            throw new UserNotFoundException(null, $email);
        }

        //get the users hashed Pass
        try {
            $hashedPass = $this->userAccessor->get($id)["hashedPass"];
        } catch (UserNotFoundException $e) {
            throw new ShouldNeverHappenException("the userID was just found by email", $e);
        }

        return $this->securityController->checkPassword($password, $hashedPass);
    }

    public function updateUsersPassword(int $id, string $newPassword, string $oldPassword): bool
    {
        //get the users hashed Pass
        $hashedPass = $this->userAccessor->get($id)["hashedPass"];

        //check if old password is correct
        if (!$this->securityController->checkPassword($oldPassword, $hashedPass)) return false;

        //validate new password
        try {
            $valid = $this->ValidationController->validate(["password" => $newPassword]);
            //if validation fails
            if ($valid !== true) {
                $reasons = $valid;
                throw new InvalidPropertyException($reasons);
            }
        } catch (ArrayIsEmptyException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("the array is not empty.", $e); // @codeCoverageIgnore
        }

        //update the database
        try {
            $this->userAccessor->update($id, array("hashedPass" => $this->securityController->hashPassword($newPassword)));
        } catch (UserNotFoundException | ValidationException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("all properties were validated before", $e); // @codeCoverageIgnore
        }

        return true;
    }

    public function requestUsersEmailChange(int $id, string $newEmail): string
    {
        //validate new email
        try {
            $valid = $this->ValidationController->validate(["email" => $newEmail]);
            //if validation fails
            if ($valid !== true) {
                $reasons = $valid;
                throw new InvalidPropertyException($reasons);
            }
        } catch (ArrayIsEmptyException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("the array is not empty.", $e); // @codeCoverageIgnore
        }

        //check if the email is free
        if (!$this->isEmailFree($newEmail)) {
            throw new InvalidPropertyException(["email" => ["IS_TAKEN"]]);
        }

        //delete old Request if there is one
        try {
            $this->ecrAccessor->deleteByUserID($id);
        } catch (EcrNotFoundException $e) {
        } //no need to do anything. its fine

        //generate a 10-char verification code
        $verificationCode = $this->securityController->generateCode(10);

        //insert the Request to the database
        try {
            $this->ecrAccessor->insert($id, $newEmail, $verificationCode);
        } catch (DuplicateUserException | DuplicateEmailException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("all parameters were checked before", $e); // @codeCoverageIgnore
        }

        //return the verification code
        return $verificationCode;
    }

    public function verifyUsersEmailChange(int $id, string $code): bool
    {
        //get the RequestID
        $RequestID = $this->ecrAccessor->findByUserID($id);
        if (is_null($RequestID)) throw new EcrNotFoundException($id, "userID");

        try {
            $Request = $this->ecrAccessor->get($RequestID);

            //check if the verification code is correct
            if ($Request["verificationCode"] !== $code) return false;

            //update the user
            try {
                $this->userAccessor->update($id, ["email" => $Request["newEmail"]]);
            } catch (ValidationException $e) { // @codeCoverageIgnore
                throw new ShouldNeverHappenException("the property array is valid", $e); // @codeCoverageIgnore
            }

            //remove the Request
            $this->ecrAccessor->delete($RequestID);
        } catch (EcrNotFoundException $e) { // @codeCoverageIgnore
            throw new ShouldNeverHappenException("the requestID was just found", $e); // @codeCoverageIgnore
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
        if (is_null($roleID)) throw new RoleNotFoundException(null, $name);
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
