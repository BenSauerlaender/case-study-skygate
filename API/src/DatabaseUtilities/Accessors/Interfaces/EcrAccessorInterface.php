<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces;

/**
 * Accessor for the "email change request" database table
 * 
 * Abstracts all SQL statements
 */
interface EcrAccessorInterface
{
    /**
     * Finds an email change request by the users id
     *
     * @param  int   $userID  The users id.
     * @return null|int the request id (or null if the request cant be found).
     */
    public function findByUserID(int $userID): ?int;

    /**
     * Finds an email change request by the users requested email
     *
     * @param  string   $email  The email, the user requested.
     * @return null|int the request id (or null if the request cant be found).
     */
    public function findByEmail(string $email): ?int;

    /**
     * Deletes an email change request from the database
     *
     * @param  int  $id The request id.
     */
    public function delete(int $id): void;

    /**
     * Deletes an email change request from the database
     *
     * @param  int  $userID    The User id.
     */
    public function deleteByUserID(int $userID): void;

    /**
     * Inserts a new email change request to the database
     *
     * @param  int    $userID               The users id.
     * @param  string $newEmail             The requested email.
     * @param  string $verification_code    The code to verify the request.
     */
    public function insert(int $userID, string $newEmail, string $verification_code): void;

    /**
     * Gets the email change request
     *
     * @param  int   $id The request id.
     * @return null|array Returns the $request object or null if the request not exist.
     *  $request = [
     *      "newEmail"          => (string)     The requested email.
     *      "verificationCode"  => (string)     The code to verify the request.
     *  ]
     */
    public function get(int $id): ?array;
}
