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
     * @param  array<string,string> $attr   The attributes as to give the new user
     *  $attr = [
     *      "email"     => (string)   The user`s e-mail. Required.
     *      "name"      => (string)   The user`s first and last name. Required.
     *      "postcode"  => (string)   The user`s postcode. Required.
     *      "city"      => (string)   The user`s city. Required.
     *      "phone"     => (string)   The user`s phone number. Required.
     *      "password"  => (string)   The user`s password. Required.
     *      "role"      => (string)   The user`s role. Options: "user", "admin". Default: "user"
     *  ]
     *@return array{id: int,verificationCode: string} The user's id and the verification code to verify the user 
     *@throws InvalidArgumentException if one or more attributes can't be validated.
     */
    public static function createUser(array $attr): array;

    /**
     * Deletes a user
     *
     * @param  int  $id the user's id 
     */
    public function deleteUser(int $id): void;

    /**
     * Updates the user's attributes
     *
     * @param  int   $id the user's id
     * @param  array<string,string> $attr   The attributes to update.
     *  $attr = [
     *      "name"      => (string)   The user`s first and last name.
     *      "postcode"  => (string)   The user`s postcode.
     *      "city"      => (string)   The user`s city.
     *      "phone"     => (string)   The user`s phone number.
     *      "role"      => (string)   The user`s role. Options: "user", "admin".
     *  ]
     */
    public function updateUser(int $id, array $attr): void;

    /**
     * Verifies the user
     * 
     * Checks if the verificationCode is correct.
     * Updates the database accordingly
     *
     * @param  int    $id                   The user's id.
     * @param  string $verificationCode     The code to verify the user
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
     * @param  int    $id                   The user's id.
     * @param  string $new_password         The user's old password.
     * @param  string $old_password         The user's new password.
     */
    public function updateUsersPassword(int $id, string $new_password, string $old_password): void;

    /**
     * Creates an request to change the user's email.
     * 
     * Validates new email.
     * Generates a verification code.
     * Write a request to change the user's email to the database.
     *
     * @param  int    $id       The user's id.
     * @param  string $newEmail The user's new email.
     * @return string           The verification code to verify the request.    
     */
    public function requestUsersEmailChange(int $id, string $newEmail): string;

    /**
     * Verifies the request to change the user's email
     * 
     * Gets the request.
     * Checks if the verification code is correct.
     * Writes the new email to the database.
     * 
     * @param  int    $id       The user's id.
     * @param  string $code     The verification code to verify the email change.
     */
    public function verifyUsersEmailChange(int $id, string $code): void;
}
