<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\Models;

use BenSauer\CaseStudySkygateApi\DatabaseGateways\RoleGateway;
use BenSauer\CaseStudySkygateApi\DatabaseInterfaces\UserInterface;
use BenSauer\CaseStudySkygateApi\DatabaseQueries\UserQuery;
use BenSauer\CaseStudySkygateApi\Utilities\Validator;
use Exception;
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
        ){
        
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

    //insert a new User to the Database
    //validates all properties
    //invalidArgumentException codes from 100 to 106
    public static function create(
        string $email, string $name, string $postcode, string $city, string $phone, string $role, string $password) : User
    {
        //validate all inputs
        if(!Validator::isEmail($email)) throw new InvalidArgumentException("No valid email",100);
        if(!Validator::isWords($name)) throw new InvalidArgumentException("No valid name",101);
        if(!Validator::isPostcode($postcode)) throw new InvalidArgumentException("No valid postcode",102);
        if(!Validator::isWords($city)) throw new InvalidArgumentException("No valid city",103);
        if(!Validator::isPhoneNumber($phone)) throw new InvalidArgumentException("No valid phone",104);
        if(!Validator::isPassword($password)) throw new InvalidArgumentException("No valid password",105);

        //check if email already exists
        //TODO

        //get role id from role name
        $role_obj = RoleGateway::findByName($role);
        // throw exception if there is no rule with this name
        if(is_null($role_obj)) throw new InvalidArgumentException("No valid role",106);
        $role_id = $role_obj->getID();

        $password_hash = password_hash($password,PASSWORD_BCRYPT);

        $verificationCode = bin2hex(random_bytes(5)); //generate a 10 charactar length hex-string 

        //insert new user to DB
        UserInterface::insert($email,$name,$postcode,$city,$phone,$password_hash,false,$verificationCode,$role_id);

        //return the new user
        return (new UserQuery())->addFilter("email",$email)->getOne();
    }

    public function delete(){
        UserInterface::delete($this->id);
    }
    //TODO: change email

    //update the DB with currently set attributes
    public function update(){
        UserInterface::update($this->id, $this->email, $this->name, $this->postcode, $this->city, $this->phone, $this->hashed_pass, $this->verified, $this->verificationCode, $this->role_id);
    }

    //verify the user by a verificationCode
    public function verify(string $verificationCode) : User {
        if($this->verified) throw new Exception("User already verified");
        if($this->verificationCode !== $verificationCode) throw new InvalidArgumentException("Invalid verification code");

        //remove verificationCode and set to verified
        $this->verificationCode = null;
        $this->verified = true;
        return $this;
    }

    //set a new name
    public function setName(string $name) : User {
        if(!Validator::isWords($name)) throw new InvalidArgumentException("No valid name",101);
        $this->name = $name;
        return $this;
    }

    //set a new postcode
    public function setPostcode(string $postcode) : User {
        if(!Validator::isPostcode($postcode)) throw new InvalidArgumentException("No valid postcode",102);
        $this->postcode = $postcode;
        return $this;
    }

    //set a new city
    public function setCity(string $city) : User {
        if(!Validator::isWords($city)) throw new InvalidArgumentException("No valid city",103);
        $this->city = $city;
        return $this;
    }
    
    //set a new phone
    public function setPhone(string $phone) : User {
        if(!Validator::isPhoneNumber($phone)) throw new InvalidArgumentException("No valid phone",104);
        $this->phone = $phone;
        return $this;
    }

    //set a new password
    public function setPassword(string $new_password, string $old_password) : User {

        //check if old password matches
        if(!$this->checkPassword($old_password)) throw new InvalidArgumentException("Invalid Password",201);

        //check if new password is safe enough
        if(!Validator::isPassword($new_password)) throw new InvalidArgumentException("No valid password",105);

        //set new password
        $this->hashed_pass = User::hashPassword($new_password);
        return $this;
    }

    //returns true if password is correct; otherwise: else
    private function checkPassword($pass) : bool {
        return password_verify($pass,$this->hashed_pass);
    }

    //create a hash for a specified password
    private static function hashPassword($pass) : string {
        return password_hash($pass,PASSWORD_BCRYPT);
    }
}
?>