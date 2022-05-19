<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\FieldNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\UniqueFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\ArrayIsEmptyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;
use PDOException;

// class to interact with the user-db-table
class MySqlUserAccessor extends MySqlAccessor implements UserAccessorInterface
{
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
    ): void {
        $sql = 'INSERT INTO user 
                    (email, name, postcode, city, phone, hashed_pass, verified, verification_code, role_id)
                VALUES
                    (:email, :name, :postcode, :city, :phone, :hashed_pass, :verified, :verification_code, :role_id);';

        try {
            $this->prepareAndExecute($sql, [
                "email" => $email,
                "name" => $name,
                "postcode" => $postcode,
                "city" => $city,
                "phone" => $phone,
                "hashed_pass" => $hashedPass,
                "verified" => $verified,
                "verification_code" => $verificationCode,
                "role_id" => $roleID
            ]);
        }
        //specify exceptions
        catch (UniqueFieldException $e) {
            throw new DuplicateEmailException("Email: $email", 0, $e);
        } catch (FieldNotFoundException $e) {
            throw new RoleNotFoundException("RoleID: $roleID", 0, $e);
        }
    }

    public function delete(int $id): void
    {
        $sql = 'DELETE
                FROM user
                WHERE user_id=:id;';

        $stmt = $this->prepareAndExecute($sql, ["id" => $id]);

        //if no user deleted
        if ($stmt->rowCount() === 0) throw new UserNotFoundException("UserID: $id");
    }

    public function update(int $id, array $fields): void
    {
        //throws ValidationExceptions if array is not valid
        $this->validateFieldArray($fields);

        $sql = 'UPDATE user ' .
            $this->getSetStatements($fields) . '
                WHERE user_id=:id;';

        try {
            $stmt = $this->prepareAndExecute($sql, $fields + ["id" => $id]);
        }
        //specify exceptions
        catch (UniqueFieldException $e) {
            $email = $fields["email"];
            throw new DuplicateEmailException("Email: $email", 0, $e);
        } catch (FieldNotFoundException $e) {
            $roleID = $fields["roleID"];
            throw new RoleNotFoundException("RoleID: $roleID", 0, $e);
        }

        //if no user updated
        if ($stmt->rowCount() === 0) throw new UserNotFoundException("UserID: $id");
    }

    public function findByEmail(string $email): ?int
    {
        $sql = 'SELECT user_id
                FROM user
                WHERE email=:email;';

        $stmt = $this->prepareAndExecute($sql, ["email" => $email]);

        $response =  $stmt->fetchAll();

        //if no user found
        if (sizeof($response) === 0) return null;

        //return the id of first and only row
        return $response[0]["user_id"];
    }

    public function get(int $id): array
    {
        $sql = 'SELECT email, name, postcode, city, phone, role_id, hashed_pass, verified, verification_code, created_at, updated_at
                FROM user 
                WHERE user_id=:id;';

        $stmt = $this->prepareAndExecute($sql, ["id" => $id]);

        $response = $stmt->fetchAll();

        //if no user found
        if ($stmt->rowCount() === 0) throw new UserNotFoundException("UserID: $id");

        //get first and only user
        $response = $response[0];

        return [
            "id" => $id,
            "email" => $response["email"],
            "name" => $response["name"],
            "postcode" => $response["postcode"],
            "city" => $response["city"],
            "phone" => $response["phone"],
            "roleID" => $response["role_id"],
            "hashedPass" => $response["hashed_pass"],
            "verified" => ($response["verified"] === 1 ? true : false),
            "verificationCode" => $response["verification_code"],
            "createdAt" => $response["created_at"],
            "updatedAt" => $response["updated_at"]
        ];
    }

    /**
     * Constructs the SQL SET statements
     *
     * @param  array  $fields   The fields array
     * @return string           returns the SET statements
     */
    private function getSetStatements(array $fields): string
    {
        $getSetClause = [
            "email" => "email = :email",
            "name" => "name = :name",
            "postcode" => "postcode = :postcode",
            "city" => "city = :city",
            "phone" => "phone = :phone",
            "roleID" => "role_id = :roleID",
            "hashedPass" => "hashed_pass = :hashedPass",
            "verified" => "verified = :verified",
            "verificationCode" => "verification_code = :verificationCode"
        ];

        //construct the set Statements
        $setStmts = "SET ";
        foreach ($fields as $key => $value) {
            $setStmts .= $getSetClause[$key] . ", ";
        }

        //remove the last ", "
        return substr($setStmts, 0, -2);
    }

    /**
     * Checks if the fields array is valid
     *
     * @param  array $fields
     * 
     * @throws ValidationException  if the array is not valid.
     *          (ArrayIsEmptyException | InvalidFieldException)
     */
    private function validateFieldArray(array $fields)
    {
        $validFields = ["email", "name", "postcode", "city", "phone", "roleID", "hashedPass", "verified", "verificationCode"];
        $fieldTypes = [
            "email" => "string",
            "name" => "string",
            "postcode" => "string",
            "city" => "string",
            "phone" => "string",
            "roleID" => "int",
            "hashedPass" => "string",
            "verified" => "bool",
            "verificationCode" => "string|null"
        ];

        //throws exception if array is empty
        if (sizeof($fields) === 0) throw new ArrayIsEmptyException("The fields array is empty.");

        //check each key-value pair
        foreach ($fields as $key => $value) {

            //throws exception if key is not supported
            if (!in_array($key, $validFields)) throw new InvalidFieldException([$key => ["UNSUPPORTED"]]);

            //continue with next key if one type matches
            foreach (explode("|", $fieldTypes[$key]) as $possibleType) {
                if ($possibleType === "null" and is_null($value)) continue 2;
                if ($possibleType === "string" and is_string($value)) continue 2;
                if ($possibleType === "int" and is_int($value)) continue 2;
                if ($possibleType === "bool" and is_bool($value)) continue 2;
            }
            //throws exception if none type matches
            throw new InvalidFieldException([$key => ["INVALID_TYPE"]]);
        }
    }
}
