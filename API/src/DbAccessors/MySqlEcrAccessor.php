<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\FieldNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateUserException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\UniqueFieldException;

class MySqlEcrAccessor extends MySqlAccessor implements EcrAccessorInterface
{
    public function findByUserID(int $userID): ?int
    {
        $sql = 'SELECT Request_id
                FROM emailChangeRequest
                WHERE user_id=:userID;';

        $stmt = $this->prepareAndExecute($sql, ["userID" => $userID]);

        $response =  $stmt->fetchAll();

        //if no Request was found: return null
        if (sizeof($response) === 0) return null;

        //return the id
        return $response[0]["Request_id"];
    }

    public function findByEmail(string $email): ?int
    {
        $sql = 'SELECT Request_id
                FROM emailChangeRequest
                WHERE new_email=:email;';

        $stmt = $this->prepareAndExecute($sql, ["email" => $email]);

        $response =  $stmt->fetchAll();

        //if no Request was found: return null
        if (sizeof($response) === 0) return null;

        //return the id
        return $response[0]["Request_id"];
    }


    public function delete(int $id): void
    {
        $sql = 'DELETE
                FROM emailChangeRequest
                WHERE Request_id=:id;';

        $stmt = $this->prepareAndExecute($sql, ["id" => $id]);

        //if no line was deleted:
        if ($stmt->rowCount() === 0) throw new EcrNotFoundException("No Request with id: " . $id . " found.");
    }

    public function deleteByUserID(int $userID): void
    {
        $sql = 'DELETE
                FROM emailChangeRequest
                WHERE user_id=:id;';


        $stmt = $this->prepareAndExecute($sql, ["id" => $userID]);

        //if no line was deleted:
        if ($stmt->rowCount() === 0) throw new EcrNotFoundException("No Request with userID: " . $userID . " found.");
    }

    public function insert(int $userID, string $newEmail, string $verification_code): void
    {
        $sql = 'INSERT INTO emailChangeRequest 
                    (user_id,new_email,verification_code)
                VALUES
                    (:id,:mail,:code);';

        try {
            $this->prepareAndExecute($sql, ["id" => $userID, "mail" => $newEmail, "code" => $verification_code]);
        }
        //specify the Exceptions    
        catch (UniqueFieldException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, "emailChangeRequest.user_id")) {
                throw new DuplicateUserException("UserID: $userID", 0, $e);
            } else if (str_contains($msg, "emailChangeRequest.new_email")) {
                throw new DuplicateEmailException("Email: $newEmail", 0, $e);
            }
        } catch (FieldNotFoundException $e) {
            throw new UserNotFoundException("UserID: $userID", 0, $e);
        }
    }

    public function get(int $id): array
    {
        $sql = 'SELECT new_email, verification_code
                FROM emailChangeRequest 
                WHERE Request_id=:id;';

        $stmt = $this->prepareAndExecute($sql, ["id" => $id]);

        $response = $stmt->fetchAll();

        //no Request found
        if (sizeof($response) === 0) throw new EcrNotFoundException("RequestID: $id");

        //get the first and only response row
        $response = $response[0];

        return [
            "newEmail" => $response["new_email"],
            "verificationCode" => $response["verification_code"]
        ];
    }
}
