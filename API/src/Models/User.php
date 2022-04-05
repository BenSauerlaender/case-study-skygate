<?php

//activate strict mode
declare(strict_types = 1);

namespace BenSauer\CaseStudySkygateApi\Models;

use BenSauer\CaseStudySkygateApi\DatabaseInterfaces\RoleInterface;
use BenSauer\CaseStudySkygateApi\DatabaseInterfaces\EmailChangeRequestInterface;
use BenSauer\CaseStudySkygateApi\DatabaseInterfaces\UserInterface;
use BenSauer\CaseStudySkygateApi\DatabaseQueries\UserQuery;
use BenSauer\CaseStudySkygateApi\Utilities\Validator;
use InvalidArgumentException;
use InvalidFunctionCallException;

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
        if(!self::isEmailNotInUse($email)) throw new InvalidArgumentException("Email already in use",110);

        //get role id from role name
        $role_obj = RoleInterface::findByName($role);
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

    //update the DB with currently set attributes
    public function update(){
        UserInterface::update($this->id, $this->email, $this->name, $this->postcode, $this->city, $this->phone, $this->hashed_pass, $this->verified, $this->verificationCode, $this->role_id);
    }

    //creates an emailChangeRequest and returns the verification code
    public function requestEmailChange(string $newEmail) : String{

        //validate new email
        if(!Validator::isEmail($newEmail)) throw new InvalidArgumentException("No valid email",100);

        //check if email already exists 
        if(!self::isEmailNotInUse($newEmail)) throw new InvalidArgumentException("Email already in use",110);

        //delete old requests
        EmailChangeRequestInterface::deleteByUserID($this->id);

        //generate a 10 charactar length hex-string 
        $verificationCode = bin2hex(random_bytes(5));

        //save the request in the DB
        EmailChangeRequestInterface::insert($this->id, $newEmail, $verificationCode);

        return $verificationCode;
    }

    //actually change the email if the code is correct
    public function verifyEmailChange($code) : User{

        //get the request
        $request = EmailChangeRequestInterface::findByUserID($this->id);

        //throw exception if there is no request
        if(is_null($request)) throw new InvalidFunctionCallException("There is no EmailChangeRequest for user_id:" . $this->id);

        //check if the code is right
        if($request["verificationCode"] !== $code) throw new InvalidArgumentException("Invalid varification code");

        //set the new Email
        $this->email = $request["newEmail"];

        //remove request
        EmailChangeRequestInterface::delete($request["id"]);

        return $this;
    }

    //returns true if the specified email is not allready in use or is requested to be come in use
    private static function isEmailNotInUse(string $email) : bool {
        if(!is_null((UserInterface::findByEmail($email)))) return false;
        if(!is_null((EmailChangeRequestInterface::findByEmail($email)))) return false;
        return true;
    }

    //verify the user by a verificationCode
    public function verify(string $verificationCode) : User {
        if($this->verified) throw new InvalidFunctionCallException("User already verified");
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