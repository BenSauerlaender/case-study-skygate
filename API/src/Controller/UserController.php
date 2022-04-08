<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\PasswordUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;

// class to represent a user of the app
class UserController implements UserControllerInterface
{
    private PasswordUtilitiesInterface $passUtil;
    private ValidatorInterface $validator;
    private UserAccessorInterface $userAccessor;

    //simple constructor to set all properties //should only be used by UserInterface
    public function __construct(PasswordUtilitiesInterface $passUtil, ValidatorInterface $validator, UserAccessorInterface $userAccessor)
    {
        $this->passUtil = $passUtil;
        $this->validator = $validator;
        $this->userAccessor = $userAccessor;
    }


    public function deleteUser(int $id): void
    {
        $this->userAccessor->delete($id);
    }

    //update the DB with currently set attributes
    public function updateUser(int $id, array $args): void
    {
        $this->validator->validate($args);

        $this->userAccessor->update($this->id, $this->email, $this->name, $this->postcode, $this->city, $this->phone, $this->hashed_pass, $this->verified, $this->verificationCode, $this->role_id);
    }

    //set a new name
    public function setName(string $name): self
    {
        if (!self::$validator->isWords($name)) throw new InvalidArgumentException("No valid name", 101);
        $this->name = $name;
        return $this;
    }

    //set a new postcode
    public function setPostcode(string $postcode): self
    {
        if (!self::$validator->isPostcode($postcode)) throw new InvalidArgumentException("No valid postcode", 102);
        $this->postcode = $postcode;
        return $this;
    }

    //set a new city
    public function setCity(string $city): self
    {
        if (!self::$validator->isWords($city)) throw new InvalidArgumentException("No valid city", 103);
        $this->city = $city;
        return $this;
    }

    //set a new phone
    public function setPhone(string $phone): self
    {
        if (!self::$validator->isPhoneNumber($phone)) throw new InvalidArgumentException("No valid phone", 104);
        $this->phone = $phone;
        return $this;
    }

    //set a new password
    public function setPassword(string $new_password, string $old_password): self
    {
        //check if old password matches
        if (!self::$utilities->checkPassword($old_password, $this->hashed_pass)) throw new InvalidArgumentException("Invalid Password", 201);

        //check if new password is safe enough
        if (!self::$validator->isPassword($new_password)) throw new InvalidArgumentException("No valid password", 105);

        //set new password
        $this->hashed_pass = self::$utilities->hashPassword($new_password);
        return $this;
    }

    //verify the user by a verificationCode
    public function verify(string $verificationCode): self
    {
        //check if user is NOT already verified and code is correct
        if ($this->verified) throw new InvalidFunctionCallException("User already verified");
        if ($this->verificationCode !== $verificationCode) throw new InvalidArgumentException("Invalid verification code");

        //remove verificationCode and set to verified
        $this->verificationCode = null;
        $this->verified = true;
        return $this;
    }

    //checks if new email is valid and free. than creates an emailChangeRequest and returns the verification code
    public function requestEmailChange(string $newEmail): String
    {
        //validate new email
        if (!self::$validator->isEmail($newEmail)) throw new InvalidArgumentException("No valid email", 100);

        // create request and return verificationCode
        return self::$utilities->createEmailChangeRequest($this->id, $newEmail);
    }

    //actually change the email if the code is correct
    public function verifyEmailChange($verificationCode): self
    {
        //verify the request and set the new Email
        $this->email = self::$utilities->verifyEmailChangeRequest($this->id, $verificationCode);

        return $this;
    }
}
