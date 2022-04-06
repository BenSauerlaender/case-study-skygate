<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Models\Interfaces;

// interface to represent a user of the app
interface UserInterface
{
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
    );

    //delete this user from data source 
    public function delete(): void;

    //update the data source with currently set attributes
    public function update(): void;

    //verify the user by a verificationCode
    public function verify(string $verificationCode): self;

    //set a new name
    public function setName(string $name): self;

    //set a new postcode
    public function setPostcode(string $postcode): self;

    //set a new city
    public function setCity(string $city): self;

    //set a new phone
    public function setPhone(string $phone): self;

    //set a new password
    public function setPassword(string $new_password, string $old_password): self;

    //creates an emailChangeRequest and returns the verification code
    public function requestEmailChange(string $newEmail): String;

    //actually change the email if the code is correct
    public function verifyEmailChange($code): self;
}
