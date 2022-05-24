<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces;

/**
 * Accessor for the user database table
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
     * @throws DBexception if there is a problem with the database.
     *          (RoleNotFoundException | DuplicateEmailException | ...)
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
     * @param  int  $id             The users id.
     * 
     * @throws DBexception          if there is a problem with the database.
     *          (UserNotFoundException | ...)
     */
    public function delete(int $id): void;

    /**
     * Updates users specified properties on the database
     *
     * @param  int   $id                        The users id.
     * @param  array<string,mixed> $properties  The users properties and new values to update.
     *  $properties = [
     *      "email"             => (string)     The users e-mail.
     *      "name"              => (string)     The users first and last name.
     *      "postcode"          => (string)     The users postcode.
     *      "city"              => (string)     The users city.
     *      "phone"             => (string)     The users phone number.
     *      "password"          => (string)     The users password.
     *      "roleID"            => (int)        The users role_id. 
     *      "hashedPass"        => (string)     The users hashed password. 
     *      "verified "         => (bool)       The users verification status.
     *      "verificationCode"  => (string)     Verification code to verify the user.
     *  ]
     * 
     * @throws DBexception                      if there is a problem with the database.
     *          (UserNotFoundException | RoleNotFoundException | DuplicateEmailException ...)
     * @throws ValidationException              if the property array is invalid.
     *          (ArrayIsEmptyException  | InvalidPropertyException)
     */
    public function update(int $id, array $properties): void;

    /**
     * Finds a users id by his email
     *
     * @param  string   $email  The users email.
     * @return null|int         The users id (or null if the user cant be found).
     * 
     * @throws DBException      if there is a problem with the database.
     */
    public function findByEmail(string $email): ?int;

    /**
     * Gets the users properties from the database
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
     *      "verified "         => (bool)       The users verification status.
     *      "verificationCode"  => (string)     The verification code to verify the user.
     *      "createdAt"         => (string)     The DateTime the user was created.
     *      "updatedAt"         => (string)     The last DateTime the user was updated.
     *  ]
     *
     * @throws DBexception if there is a problem with the database.
     *          (UserNotFoundException | ...)
     */
    public function get(int $id): array;
}
