<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Models;

use BenSauer\CaseStudySkygateApi\Models\Interfaces\UserInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\UserUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidFunctionCallException;
use InvalidArgumentException;

// class to represent a user of the app
class User implements UserInterface
{
    public static ?UserUtilitiesInterface $utilities = null;
    public static ?ValidatorInterface $validator = null;
    public static ?UserAccessorInterface $userAccessor = null;

    //set up the class with implementations of interfaces, that are needed
    public static function setUp(UserUtilitiesInterface $utilities, UserAccessorInterface $userAccessor, ValidatorInterface $validator): void
    {
        self::$utilities = $utilities;
        self::$userAccessor = $userAccessor;
        self::$validator = $validator;
    }

    private int $id;
    private string $email;
    private string $name;
    private string $postcode;
    private string $city;
    private string $phone;
    private string $hashed_pass;
    private bool $verified;
    private ?string $verificationCode;
    private int $role_id;

    //simple constructor to set all properties //should only be used by UserInterface
    public function __construct(
        int $id,
        string $email,
        string $name,
        string $postcode,
        string $city,
        string $phone,
        string $hashed_pass,
        bool $verified,
        ?string $verificationCode,
        int $role_id
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->phone = $phone;
        $this->hashed_pass = $hashed_pass;
        $this->verified = $verified;
        $this->verificationCode = $verificationCode;
        $this->role_id = $role_id;
    }

    public function delete(): void
    {
        self::$userAccessor->delete($this->id);
    }

    //update the DB with currently set attributes
    public function update(): void
    {
        self::$userAccessor->update($this->id, $this->email, $this->name, $this->postcode, $this->city, $this->phone, $this->hashed_pass, $this->verified, $this->verificationCode, $this->role_id);
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
