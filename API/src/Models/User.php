<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\Models;

use BenSauer\CaseStudySkygateApi\DatabaseGateways\RoleGateway;
use BenSauer\CaseStudySkygateApi\Utilities\Validator;
use InvalidArgumentException;

// class to represent a user of the app
class User {
    private int $id;
    private string $email;
    private string $name;
    private string $postcode;
    private string $city;
    private string $phone;
    private string $hashed_pass;
    private bool $verified;
    private int $role_id;

    //simple constructor to set all properties
    public function __construct(
        int $id, 
        string $email, 
        string $name, 
        string $postcode, 
        string $city, 
        string $phone, 
        string $hashed_pass, 
        bool $verified, 
        int $role_id
        ){
        
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->phone = $phone;
        $this->hashed_pass = $hashed_pass;
        $this->verified = $verified;
        $this->role_id = $role_id;
    }

    //insert a new User to the Database
    //validates all properties
    //invalidArgumentException codes from 100 to 106
    public static function create(
        string $email, string $name, string $postcode, string $city, string $phone, string $role, string $password)
    {
        //validate all inputs
        if(!Validator::isEmail($email)) throw new InvalidArgumentException("No valid email",100);
        if(!Validator::isWords($name)) throw new InvalidArgumentException("No valid name",101);
        if(!Validator::isPostcode($postcode)) throw new InvalidArgumentException("No valid postcode",102);
        if(!Validator::isWords($city)) throw new InvalidArgumentException("No valid city",103);
        if(!Validator::isPhoneNumber($phone)) throw new InvalidArgumentException("No valid phone",104);
        if(!Validator::isPassword($password)) throw new InvalidArgumentException("No valid password",105);

        //get role id from role name
        $role_obj = RoleGateway::findByName($role);
        // throw exception if there is no rule with this name
        if(is_null($role_obj)) throw new InvalidArgumentException("No valid role",106);
        $role_id = $role_obj->getID();


    }
}
?>