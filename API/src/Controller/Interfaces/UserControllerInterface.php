<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

/**
 * Controller for all User related stuff
 */
interface UserControllerInterface
{
    /**
     * Creates a new user
     * 
     * Validates all attributes.
     * Hashs the password.
     * Writes the new user to the database.
     *
     * @param  array<string,string> $attr   The attributes to give the new user
     *  $attr = [
     *      "email"     => (string)   The users e-mail. Required.
     *      "name"      => (string)   The users first and last name. Required.
     *      "postcode"  => (string)   The users postcode. Required.
     *      "city"      => (string)   The users city. Required.
     *      "phone"     => (string)   The users phone number. Required.
     *      "password"  => (string)   The users password. Required.
     *      "role"      => (string)   The users role. Options: "user", "admin". Default: "user"
     *  ]
     * @return array{id: int,verificationCode: string} The user's id and the verification code to verify the user 
     * 
     * @throws InvalidArgumentException (1)     if there are missing attributes.
     * @throws InvalidArgumentException (2)     if at least one attribute is not supported
     * @throws InvalidArgumentException (3)     if at least one attribute is invalid.
     * @throws InvalidArgumentException (4)     if the email is not free.
     */
    public function createUser(array $attr): array;

    /**
     * Deletes a user
     *
     * @param  int  $id the user's id 
     * @throws OutOfRangeException (1)      if the id is negative.
     * @throws InvalidArgumentException (1) if there is no user with this id.
     */
    public function deleteUser(int $id): void;

    /**
     * Updates the user's attributes
     *
     * @param  int   $id the users id
     * @param  array<string,string> $attr   The attributes to update.
     *  $attr = [
     *      "name"      => (string)   The users first and last name.
     *      "postcode"  => (string)   The users postcode.
     *      "city"      => (string)   The users city.
     *      "phone"     => (string)   The users phone number.
     *      "role"      => (string)   The users role. Options: "user", "admin".
     *  ]
     * 
     * @throws InvalidArgumentException (1)     if there are no attributes.
     * @throws InvalidArgumentException (2)     if at least one attribute is not supported
     * @throws InvalidArgumentException (3)     if at least one attribute is invalid.
     * @throws OutOfRangeException (1)          if the id is negative.
     */
    public function updateUser(int $id, array $attr): void;

    /**
     * Verifies the user
     * 
     * Checks if the verificationCode is correct.
     * Updates the database accordingly
     *
     * @param  int    $id                   The users id.
     * @param  string $verificationCode     The code to verify the user
     * 
     * @throws InvalidArgumentException (1)    if there is no user with this id 
     * @throws InvalidArgumentException (2)    if the verificationCode is wrong.
     * @throws OutOfRangeException (1)         if the id is negative.
     * @throws BadMethodCallException (1)      if the user is already verified.
     */
    public function verifyUser(int $id, string $verificationCode): void;

    /**
     * Changes the users password
     * 
     * Checks if the old password matches.
     * Validates the new password.
     * Hashs the new password.
     * Write the new password to the database.
     *
     * @param  int    $id                   The users id.
     * @param  string $new_password         The users old password.
     * @param  string $old_password         The users new password.
     * 
     * @throws InvalidArgumentException (1)    if there is no user with the id 
     * @throws InvalidArgumentException (2)    if the old password is incorrect.
     * @throws InvalidArgumentException (3)    if the new password is not valid.
     * @throws OutOfRangeException (1)         if the id is negative.
     */
    public function updateUsersPassword(int $id, string $new_password, string $old_password): void;

    /**
     * Creates an request to change the users email.
     * 
     * Validates new email.
     * Generates a verification code.
     * Write a request to change the users email to the database.
     *
     * @param  int    $id       The users id.
     * @param  string $newEmail The users new email.
     * @return string           The verification code to verify the request.   
     *  
     * @throws InvalidArgumentException (1)    if there is no user with the id.
     * @throws InvalidArgumentException (2)    if the email is not valid.
     * @throws InvalidArgumentException (3)    or already in use.
     * @throws OutOfRangeException (1)         if the id is negative.
     */
    public function requestUsersEmailChange(int $id, string $newEmail): string;

    /**
     * Verifies the request to change the user's email
     * 
     * Gets the request.
     * Checks if the verification code is correct.
     * Writes the new email to the database.
     * 
     * @param  int    $id       The users id.
     * @param  string $code     The verification code to verify the email change.
     * 
     * @throws InvalidArgumentException (1)    if there is no email change request with this id or the verificationCode is incorrect.
     * @throws OutOfRangeException (1)         if the id is negative.
     */
    public function verifyUsersEmailChange(int $id, string $code): void;
}
