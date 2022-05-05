<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces;

/**
 * Accessor for the "email change Request" database table
 * 
 * Abstracts all SQL statements
 */
interface EcrAccessorInterface
{
    /**
     * Finds an email change Request by the users id
     *
     * @param  int   $userID        The users id.
     * @return null|int             The Request id (or null if the Request cant be found).
     * 
     * @throws DBexception    if there is a problem with the database.
     */
    public function findByUserID(int $userID): ?int;

    /**
     * Finds an email change Request by the users Requested email
     *
     * @param  string   $email      The email, the user Requested.
     * @return null|int             The Request id (or null if the Request cant be found).
     * 
     * @throws DBexception    if there is a problem with the database.
     */
    public function findByEmail(string $email): ?int;

    /**
     * Deletes an email change Request from the database
     *
     * @param  int  $id                     The Request id.
     * 
     * @throws DBexception            if there is a problem with the database.
     *          (EcrNotFoundException | ...)
     */
    public function delete(int $id): void;

    /**
     * Deletes an email change Request from the database
     *
     * @param  int  $userID                 The User id.
     * 
     * @throws DBexception            if there is a problem with the database.
     *          (EcrNotFoundException | ...)
     */
    public function deleteByUserID(int $userID): void;

    /**
     * Inserts a new email change Request to the database
     *
     * @param  int    $userID               The users id.
     * @param  string $newEmail             The Requested email.
     * @param  string $verification_code    The code to verify the Request.
     * 
     * @throws DBexception            if there is a problem with the database.
     *          (UserNotFoundException | DuplicateUserException | DuplicateEmailException | ...)
     */
    public function insert(int $userID, string $newEmail, string $verification_code): void;

    /**
     * Gets the email change Request
     *
     * @param  int   $id    The Request id.
     * @return array        returns the $response array.
     *  $response = [
     *      "newEmail"          => (string)     The Requested email.
     *      "verificationCode"  => (string)     The code to verify the Request.
     *  ]
     * 
     * @throws DBexception            if there is a problem with the database.
     *          (EcrNotFoundException | ...)
     */
    public function get(int $id): array;
}
