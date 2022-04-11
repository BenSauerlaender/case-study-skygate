<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\PasswordUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use InvalidArgumentException;
use RuntimeException;

class UserController implements UserControllerInterface
{
    private PasswordUtilitiesInterface $passUtil;
    private ValidatorInterface $validator;
    private UserAccessorInterface $userAccessor;
    private RoleAccessorInterface $roleAccessor;
    private EcrAccessorInterface $ecrAccessor;

    //simple constructor to set all properties //should only be used by UserInterface
    public function __construct(PasswordUtilitiesInterface $passUtil, ValidatorInterface $validator, UserAccessorInterface $userAccessor, RoleAccessorInterface $roleAccessor, EcrAccessorInterface $ecrAccessor)
    {
        $this->passUtil = $passUtil;
        $this->validator = $validator;
        $this->userAccessor = $userAccessor;
        $this->roleAccessor = $roleAccessor;
        $this->ecrAccessor = $ecrAccessor;
    }


    public function createUser(array $attr): array
    {
        //checks if all required attributes exists
        if (!$this->array_keys_exists(["email", "name", "postcode", "city", "phone", "password"], $attr)) {
            throw new InvalidArgumentException("There are missing attributes");
        }

        //validate all (except "role") the attributes.
        $this->validator->validate(\array_diff_key($attr, ["role" => ""]));

        //check if the email is free
        if (!$this->isEmailFree($attr["email"])) {
            throw new  InvalidAttributeException("The email: " . $attr["email"] . " is already in use.", 110);
        }

        //get the role id. Default role is "user"
        $roleName = $attr["role"] ?? "user";
        $roleID = $this->roleAccessor->findByName($roleName);
        if (is_null($roleID)) throw new InvalidAttributeException("The role '" . $attr["role"] . " is not a valid", 106);

        //hash the password
        $hashedPassword = $this->passUtil->hashPassword($attr["password"]);

        //generate the 10-char verification code
        $verificationCode = $this->generateCode(10);

        //insert the new user into the database
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

        //find the just created user in the database and return his id.
        $id = $this->userAccessor->findByEmail($attr["email"]);
        if (is_null($id)) throw new RuntimeException("The just created user(email: " . $attr["email"] . ") can't be found in the database.");
        return array("id" => $id, "verificationCode" => $verificationCode);
    }


    public function deleteUser(int $id): void
    {
        $this->userAccessor->delete($id);
    }

    public function updateUser(int $id, array $attr): void
    {
        //validate all (except "role") the attributes.
        $this->validator->validate(\array_diff_key($attr, ["role" => ""]));

        //replace role name by its id
        if (array_key_exists("role", $attr)) {
            $attr["roleID"] = $this->getRoleID($attr["role"]);
            unset($attr["role"]);
        }

        //update the database
        $this->userAccessor->update($id, $attr);
    }


    public function verifyUser(int $id, string $verificationCode): void
    {
        //get the users attributes
        $user = $this->userAccessor->get($id);
        if (is_null($user)) throw new InvalidArgumentException("There is no user with id: " . $id);

        //check if the verification code is correct
        if ($user["verificationCode"] !== $verificationCode) throw new InvalidArgumentException("Verification code is not correct.");

        //update the database
        $this->userAccessor->update($id, array("verificationCode" => null, "verified" => true));
    }

    public function updateUsersPassword(int $id, string $newPassword, string $oldPassword): void
    {
        //get the users attributes
        $user = $this->userAccessor->get($id);
        if (is_null($user)) throw new InvalidArgumentException("There is no user with id: " . $id);

        //check if old password is correct
        if (!$this->passUtil->checkPassword($oldPassword, $user["hashedPass"])) throw new InvalidArgumentException("Old Password is incorrect");

        //validate new password
        $this->validator->validate(array("password" => $newPassword));

        //update the database
        $this->userAccessor->update($id, array("hashedPass" => $this->passUtil->hashPassword($newPassword)));
    }

    public function requestUsersEmailChange(int $id, string $newEmail): string
    {
        //get the users attributes
        $user = $this->userAccessor->get($id);
        if (is_null($user)) throw new InvalidArgumentException("There is no user with id: " . $id);

        //validate new email
        $this->validator->validate(array("email" => $newEmail));

        //check if the email is free
        if (!$this->isEmailFree($newEmail)) {
            throw new  InvalidAttributeException("The email: " . $newEmail . " is already in use.", 110);
        }

        //delete old requests
        $this->ecrAccessor->deleteByUserID($id);

        //generate the 10-char verification code
        $verificationCode = $this->generateCode(10);

        //insert the request to the database
        $this->ecrAccessor->insert($id, $newEmail, $verificationCode);

        //return the verification code
        return $verificationCode;
    }

    public function verifyUsersEmailChange(int $id, string $code): void
    {
        //get the request
        $requestID = $this->ecrAccessor->findByUserID($id);
        if (is_null($requestID)) throw new InvalidArgumentException("There is no email change request for the user with id:" . $id);
        $request = $this->ecrAccessor->get($requestID);
        if (is_null($request)) throw new RuntimeException("The just found request with id: " . " can now not found anymore.");

        //check if the verification code is correct
        if ($request["verificationCode"] !== $code) {
            throw new InvalidArgumentException("Verification code is incorrect");
        }

        //update the user
        $this->userAccessor->update($id, array("email" => $request["newEmail"]));

        //remove the request
        $this->ecrAccessor->delete($requestID);
    }

    /**
     * Gets the id of a specified role name
     *
     * @param  string $name The roles name.
     * @return int  Returns the roles id.
     * @throws InvalidAttributeException if such a role cant be found
     */
    private function getRoleID(string $name): int
    {
        $roleID = $this->roleAccessor->findByName($name);
        if (is_null($roleID)) throw new InvalidAttributeException("The role '" . $name . " is not a valid", 106);
        return $roleID;
    }

    /**
     * Generates a semi random hexadecimal string
     *
     * @param  int    $length   The length of the output string.
     * @return string   A string out of hexadecimal digits.
     */
    private function generateCode(int $length): string
    {
        return bin2hex(random_bytes($length / 2));
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
