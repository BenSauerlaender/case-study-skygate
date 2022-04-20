<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces;


/**
 * Accessor for the user database table
 * 
 * Abstracts all SQL statements
 */
interface UserAccessorInterface
{
    /**
     * insert a new user to the database
     *
     * @param  string       $email                The users email.
     * @param  string       $name                 The users first and last name.
     * @param  string       $postcode             The users postcode.
     * @param  string       $city                 The users city.
     * @param  string       $phone                The users phone number.
     * @param  string       $hashed_pass          The users hashed password.
     * @param  bool         $verified             True if the user is verified.
     * @param  string|null  $verificationCode     Code to verify the user.
     * @param  int          $roleID               The ID of the users permission role.
     * 
     * @throws InvalidArgumentException if the email is already taken or the role is not existing
     */
    public function insert(
        string $email,
        string $name,
        string $postcode,
        string $city,
        string $phone,
        string $hashedPass,
        bool $verified,
        ?string $verificationCode,
        int $roleID
    ): void;

    /**
     * Deletes an user from the database
     *
     * @param  int  $id     The users id.
     * 
     * @throws InvalidArgumentException if there is no user with this id.
     */
    public function delete(int $id): void;


    /**
     * Updates users specified attributes to the database
     *
     * @param  int   $id    The users id.
     * @param  array<string,mixed> $attr   The users attributes to update
     *  $attr = [
     *      "email"             => (string)     The users e-mail.
     *      "name"              => (string)     The users first and last name.
     *      "postcode"          => (string)     The users postcode.
     *      "city"              => (string)     The users city.
     *      "phone"             => (string)     The users phone number.
     *      "password"          => (string)     The users password.
     *      "roleID"            => (int)        The users role_id. 
     *      "hashedPass"        => (string)     The users hashed password. 
     *      "verified "         => (bool)       Is the user verified.
     *      "verificationCode"  => (string)     Verification code to verify the user.
     *  ]
     * 
     * @throws InvalidArgumentException if there is no user with this id or the email is already taken or the $attr array has invalid keys or types.
     */
    public function update(int $id, array $attr): void;

    /**
     * Finds a user by his email
     *
     * @param  string   $email  The users email.
     * @return null|int the users id (or null if the user cant be found).
     */
    public function findByEmail(string $email): ?int;

    /**
     * Gets the users attributes from the database
     *
     * @param  int  $id                     The users id.
     * @return array<string,mixed>          Returns the $user array.
     *  $user = [
     *      "id"                => (int)        The users id. 
     *      "email"             => (string)     The users e-mail.
     *      "name"              => (string)     The users first and last name.
     *      "postcode"          => (string)     The users postcode.
     *      "city"              => (string)     The users city.
     *      "phone"             => (string)     The users phone number.
     *      "roleID"            => (string)     The users roleID.
     *      "hashedPass"        => (string)     The users hashed password. 
     *      "verified "         => (bool)       Is the user verified.
     *      "verificationCode"  => (string)     Verification code to verify the user.
     *      "createdAt"         => (string)     The DateTime the user was created.
     *      "updatedAt"         => (string)     The last DateTime the user was updated.
     *  ]
     *
     * @throws InvalidArgumentException if there is no request with this id.
     */
    public function get(int $id): array;
}
