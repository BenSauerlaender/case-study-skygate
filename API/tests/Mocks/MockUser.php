<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Mocks;

use BenSauer\CaseStudySkygateApi\Models\Interfaces\UserInterface;

class MockUser implements UserInterface
{

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
    }
    public function delete(): void
    {
    }

    public function update(): void
    {
    }

    public function setName(string $name): self
    {
        return $this;
    }

    public function setPostcode(string $postcode): self
    {
        return $this;
    }

    public function setCity(string $city): self
    {
        return $this;
    }

    public function setPhone(string $phone): self
    {
        return $this;
    }

    public function setPassword(string $new_password, string $old_password): self
    {
        return $this;
    }

    public function verify(string $verificationCode): self
    {
        return $this;
    }

    public function requestEmailChange(string $newEmail): String
    {

        return "";
    }

    public function verifyEmailChange($verificationCode): self
    {
        return $this;
    }
}
