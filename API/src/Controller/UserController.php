<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\SecurityUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
use RuntimeException;

class UserController implements UserControllerInterface
{
    private SecurityUtilitiesInterface $securityUtil;
    private ValidatorInterface $validator;
    private UserAccessorInterface $userAccessor;
    private RoleAccessorInterface $roleAccessor;
    private EcrAccessorInterface $ecrAccessor;

    //simple constructor to set all properties //should only be used by UserInterface
    public function __construct(SecurityUtilitiesInterface $securityUtil, ValidatorInterface $validator, UserAccessorInterface $userAccessor, RoleAccessorInterface $roleAccessor, EcrAccessorInterface $ecrAccessor)
    {
        $this->securityUtil = $securityUtil;
        $this->validator = $validator;
        $this->userAccessor = $userAccessor;
        $this->roleAccessor = $roleAccessor;
        $this->ecrAccessor = $ecrAccessor;
    }


    public function createUser(array $attr): array
    {
        //checks if all required attributes exists
        if (!$this->array_keys_exists(["email", "name", "postcode", "city", "phone", "password"], $attr)) {
            throw new InvalidArgumentException("There are missing attributes", 1);
        }

        //validate all (except "role") attributes.
        try {
            $this->validator->validate(\array_diff_key($attr, ["role" => ""]));
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("There are unsupported attributes", 2, $e);
        } catch (InvalidAttributeException $e) {
            throw new InvalidArgumentException("There is at least one invalid attribute", 3, $e);
        }

        //check if the email is free
        if (!$this->isEmailFree($attr["email"])) {
            throw new  InvalidArgumentException("The email: " . $attr["email"] . " is already in use.", 4);
        }

        //get the role id. Default role is "user"
        $roleName = $attr["role"] ?? "user";
        try {
            $roleID = $this->getRoleID($roleName);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("There is at least one invalid attribute", 3, $e);
        }

        //hash the password
        $hashedPassword = $this->securityUtil->hashPassword($attr["password"]);

        try {
            //generate the 10-char verification code
            $verificationCode = $this->securityUtil->generateCode(10);
        } catch (OutOfRangeException $e) {
            //It is normally not possible, that generateCode(10) throws an InvalidArgumentException
            throw new RuntimeException("", 0, $e);
        }

        //insert the new user into the database
        try {
            $this->userAccessor->insert(
                $attr["email"],
                $attr["name"],
                $attr["postcode"],
                $attr["city"],
                $attr["phone"],
                $hashedPassword,
                false,
                $verificationCode,
                $roleID
            );
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException("userAccessor->insert throw exception, even through all prerequisites were checked.", 0, $e);
        }

        //find the just created user in the database and return his id.
        $id = $this->userAccessor->findByEmail($attr["email"]);
        if (is_null($id)) throw new RuntimeException("The just created user(email: " . $attr["email"] . ") can't be found in the database.");
        return array("id" => $id, "verificationCode" => $verificationCode);
    }


    public function deleteUser(int $id): void
    {
        if ($id < 0) throw new OutOfRangeException($id . "is not a valid id", 1);
        $this->userAccessor->delete($id);
    }

    public function updateUser(int $id, array $attr): void
    {
        if ($id < 0) throw new OutOfRangeException($id . "is not a valid id", 1);

        if (sizeof($attr) === 0) throw new InvalidArgumentException("The attribute array is empty", 1);

        if (array_key_exists("password", $attr)) throw new InvalidArgumentException("To change the password use updateUserPassword", 2);
        if (array_key_exists("email", $attr)) throw new InvalidArgumentException("To change the email use requestUsersEmailChange", 2);

        //validate all (except "role") attributes.
        try {
            $this->validator->validate(\array_diff_key($attr, ["role" => ""]));
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("There are unsupported attributes", 2, $e);
        } catch (InvalidAttributeException $e) {
            throw new InvalidArgumentException("There is at least one invalid attribute", 3, $e);
        }

        //replace role name by its id
        if (array_key_exists("role", $attr)) {
            try{
                $attr["roleID"] = $this->getRoleID($attr["role"]);
            }catch(InvalidArgumentException $e){
                throw new InvalidArgumentException("Role is invalid",3,$e)
            }
            unset($attr["role"]);
        }

        //update the database
        try {
            $this->userAccessor->update($id, $attr);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException("userAccessor->update throws an exception, even though all perquisites are checked", 0, $e);
        }
    }


    public function verifyUser(int $id, string $verificationCode): void
    {
        if ($id < 0) throw new OutOfRangeException($id . "is not a valid id", 1);

        //get the users attributes
        $user = $this->userAccessor->get($id);

        //check if the user is verified already
        if ($user["verified"]) throw new BadMethodCallException("The User (with id: " . $id . ") is already verified", 1);

        //check if the verification code is correct
        if ($user["verificationCode"] !== $verificationCode) throw new InvalidArgumentException("Verification code is not correct.", 2);

        //update the database
        try{
        $this->userAccessor->update($id, array("verificationCode" => null, "verified" => true));
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException("userAccessor->update throws an exception, even though all perquisites are checked", 0, $e);
        }
    }

    public function updateUsersPassword(int $id, string $newPassword, string $oldPassword): void
    {
        if ($id < 0) throw new OutOfRangeException($id . "is not a valid id", 1);

        //get the users attributes
        $user = $this->userAccessor->get($id);

        //check if old password is correct
        if (!$this->securityUtil->checkPassword($oldPassword, $user["hashedPass"])) throw new InvalidArgumentException("Old Password is incorrect", 2);

        try {
            //validate new password
            $this->validator->validate(array("password" => $newPassword));
        } catch (InvalidArgumentException $e) {
            if($e->getCode(1)){
                throw new RuntimeException("Validator throw InvalidArgumentException, but its provided only password", 0, $e);
            }else{
                throw new InvalidArgumentException("The new Password is not valid", 3);
            }
        }

        //update the database
        try{
        $this->userAccessor->update($id, array("hashedPass" => $this->securityUtil->hashPassword($newPassword)));
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException("userAccessor->update throws an exception, even though all perquisites are checked", 0, $e);
        }
    }

    public function requestUsersEmailChange(int $id, string $newEmail): string
    {
        if ($id < 0) throw new OutOfRangeException($id . "is not a valid id", 1);

        //get the users attributes
        $user = $this->userAccessor->get($id);

        try {
            //validate new email
            $this->validator->validate(array("email" => $newEmail));
        } catch (InvalidArgumentException $e) {
            if($e->getCode(1)){
                throw new RuntimeException("Validator throw InvalidArgumentException, but its provided only email", 0, $e);
            }else{
                throw new InvalidArgumentException("The new email is not valid", 3);
            }
        }

        //check if the email is free
        if (!$this->isEmailFree($newEmail)) {
            throw new  InvalidAttributeException("The email: " . $newEmail . " is already in use.", 110);
        }

        //delete old request if there is one
        try {
            $this->ecrAccessor->deleteByUserID($id);
        } catch (InvalidArgumentException $e) {
        } //no need to do something. its fine

        try {
            //generate the 10-char verification code
            $verificationCode = $this->securityUtil->generateCode(10);
        } catch (InvalidArgumentException $e) {
            //It is normally not possible, that generateCode(10) throws an InvalidArgumentException
            throw new RuntimeException("", 0, $e);
        }
        //insert the request to the database
        try {
            $this->ecrAccessor->insert($id, $newEmail, $verificationCode);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException("Ecr Accessor throw exception, even through everything was checked before", 0, $e);
        }

        //return the verification code
        return $verificationCode;
    }

    public function verifyUsersEmailChange(int $id, string $code): void
    {
        if ($id < 0) throw new OutOfRangeException($id . "is not a valid id");

        //get the request
        $requestID = $this->ecrAccessor->findByUserID($id);
        if (is_null($requestID)) throw new InvalidArgumentException("There is no email change request for the user with id:" . $id);

        try {
            $request = $this->ecrAccessor->get($requestID);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException("The just found request with id: " . " can now not be found anymore.", 0, $e);
        }

        //check if the verification code is correct
        if ($request["verificationCode"] !== $code) {
            throw new InvalidArgumentException("Verification code is incorrect");
        }

        //update the user
        $this->userAccessor->update($id, array("email" => $request["newEmail"]));

        //remove the request
        try {
            $this->ecrAccessor->delete($requestID);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException("Cant delete the just found ECR", 0, $e);
        }
    }

    /**
     * Gets the id of a specified role name
     *
     * @param  string $name The roles name.
     * @return int  Returns the roles id.
     * 
     * @throws InvalidArgumentException (1) if such a role cant be found.
     */
    private function getRoleID(string $name): int
    {
        if (is_null($roleID)) throw new InvalidArgumentException("The role '" . $name . " is not a valid role", 1);
        return $roleID;
    }


    /**
     * Checks if the specified email if free to use
     * 
     * Checks if the email is used by a user.
     * Checks if the email is requested by a user.
     *
     * @param  string $email    The email to check for.
     * @return bool Returns true if the email is free, otherwise false.
     */
    private function isEmailFree(string $email): bool
    {
        if (!is_null(($this->userAccessor->findByEmail($email)))) return false;
        if (!is_null(($this->ecrAccessor->findByEmail($email)))) return false;
        return true;
    }

    /**
     * Checks if all keys exists in an array
     * 
     * Calls array_key_exists() for each key.
     *
     * @param  string[] $keys   A list of all keys to check.
     * @param  array $arr       The array to check on.
     * @return bool Returns true if all keys exists, false otherwise.
     */
    private function array_keys_exists(array $keys, array $arr): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $arr)) return false;
        }
        return true;
    }
}
