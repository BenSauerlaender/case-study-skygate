<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DatabaseException;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class MySqlEcrAccessor extends MySqlAccessor implements EcrAccessorInterface
{
    public function findByUserID(int $userID): ?int
    {
        $stmt = $this->pdo->prepare('
            SELECT request_id
            FROM emailChangeRequest
            WHERE user_id=:userID;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            $stmt->execute(["userID" => $userID]);
        } catch (PDOException $e) {
            throw new DatabaseException("", 1, $e);
        }

        $response =  $stmt->fetchAll();

        //if no request was found: return null
        if (sizeof($response) === 0) return null;

        //return the id
        return $response[0]["request_id"];
    }

    public function findByEmail(string $email): ?int
    {
        $stmt = $this->pdo->prepare('
            SELECT request_id
            FROM emailChangeRequest
            WHERE new_email=:email;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            $stmt->execute(["email" => $email]);
        } catch (PDOException $e) {
            throw new DatabaseException("", 1, $e);
        }

        $response =  $stmt->fetchAll();

        //if no request was found: return null
        if (sizeof($response) === 0) return null;

        //return the id
        return $response[0]["request_id"];
    }


    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('
            DELETE
            FROM emailChangeRequest
            WHERE request_id=:id;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            $stmt->execute(["id" => $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("", 1, $e);
        }

        //if no line was deleted:
        if ($stmt->rowCount() === 0) throw new InvalidArgumentException("No request with id: " . $id . " found.", 1);
    }

    /**
     * Deletes an email change request from the database
     *
     * @param  int  $userID    The User id.
     */
    public function deleteByUserID(int $userID): void
    {
        $stmt = $this->pdo->prepare('
            DELETE
            FROM emailChangeRequest
            WHERE user_id=:id;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            $stmt->execute(["id" => $userID]);
        } catch (PDOException $e) {
            throw new DatabaseException("", 1, $e);
        }

        //if no line was deleted:
        if ($stmt->rowCount() === 0) throw new InvalidArgumentException("No request with userID: " . $userID . " found.", 1);
    }

    public function insert(int $userID, string $newEmail, string $verification_code): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO emailChangeRequest 
                (user_id,new_email,verification_code)
            VALUES
                (:id,:mail,:code);
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            //execute the insert
            $stmt->execute(["id" => $userID, "mail" => $newEmail, "code" => $verification_code]);
        }

        //catch exceptions that are caused by invalid arguments
        catch (PDOException $e) {

            // userID Duplicate
            if (
                str_contains($e->getMessage(), "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry") and
                str_contains($e->getMessage(), "emailChangeRequest.user_id")
            ) {
                throw new InvalidArgumentException("There is already a request for user with userID: " . $userID, 1, $e);
            }

            // Email duplicate
            else if (
                str_contains($e->getMessage(), "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry") and
                str_contains($e->getMessage(), "for key 'emailChangeRequest.new_email'")
            ) {
                throw new InvalidArgumentException("There is already a request with email: " . $newEmail, 2, $e);
            }

            //no user with userID
            else if (
                str_contains($e->getMessage(), "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`api_db_test`.`emailChangeRequest`, CONSTRAINT `emailChangeRequest_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`))")
            ) {
                throw new InvalidArgumentException("There is no user with userID: " . $userID, 3, $e);
            }

            //everything else
            else {
                throw new DatabaseException("", 1, $e);
            }
        }
    }

    public function get(int $id): array
    {
        $stmt = $this->pdo->prepare('
            SELECT new_email, verification_code
            FROM emailChangeRequest 
            WHERE request_id=:id;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            $stmt->execute(["id" => $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("", 1, $e);
        }

        $response = $stmt->fetchAll();

        //no request found
        if (sizeof($response) === 0) throw new InvalidArgumentException("There is no request with id: " . $id, 1);

        //get the first and only response row
        $response = $response[0];

        return [
            "newEmail" => $response["new_email"],
            "verificationCode" => $response["verification_code"]
        ];
    }
}
