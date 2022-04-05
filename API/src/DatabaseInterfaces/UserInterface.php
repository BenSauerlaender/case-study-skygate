<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseInterfaces;


// interface to interact with the user-db-table
class UserInterface { //TODO fill methods
    public static function insert(string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id) { }
    public static function delete(int $id){}
    public static function update(int $id, string $email, string $name, string $postcode, string $city, string $phone, string $hashed_pass, bool $verified, string $verificationCode, int $role_id){ }
}
?>
