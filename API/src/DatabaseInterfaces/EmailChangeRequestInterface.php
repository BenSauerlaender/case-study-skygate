<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\DatabaseInterfaces;

use BenSauer\CaseStudySkygateApi\Models\Role;

// functions to interact with the changeEmailRequest-db-table
class EmailChangeRequestInterface {
    //find Request by userID and return id, newEmail and verificationCode
    public static function findByUserID(int $user_id) : ?array{}

    //find Request for specified email and return id or null if there is no user with this email
    public static function findByEmail(string $email) : ?int{}

    //delete request by ID
    public static function delete(int $id){}

    //delete request by userID
    public static function deleteByUserID(int $user_id){}
    
    //insert a new request
    public static function insert(int $user_id, string $new_email, string $verification_code){ }

}
?>

