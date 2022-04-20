<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

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
        $stmt = $this->pdo->prepare('
            INSERT INTO user 
                (email, name, postcode, city, phone, hashed_pass, verified, verification_code, role_id)
            VALUES
                (:email, :name, :postcode, :city, :phone, :hashed_pass, :verified, :verification_code, :role_id);
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            //execute the insert
            $stmt->execute([
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

        //catch exceptions that are caused by invalid arguments
        catch (PDOException $e) {

            // Email duplicate
            if (
                str_contains($e->getMessage(), "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry") and
                str_contains($e->getMessage(), "for key 'user.email'")
            ) {
                throw new InvalidArgumentException("There is already a user with email: " . $email, 0, $e);
            }

            //no role with roleID
            else if (
                str_contains($e->getMessage(), "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`api_db_test`.`user`, CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`))")
            ) {
                throw new InvalidArgumentException("There is no role with roleID: " . $roleID, 0, $e);
            }

            //everything else
            else {
                throw $e;
            }
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('
            DELETE
            FROM user
            WHERE user_id=:id;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        $stmt->execute(["id" => $id]);

        if ($stmt->rowCount() === 0) throw new InvalidArgumentException("No user with id: " . $id . " found.");
    }

    public function update(int $id, array $attr): void
    {
        //throws exception if attribute array is not valid
        $this->checkAttrArray($attr);

        $stmt = $this->pdo->prepare('
            UPDATE user
            ' . $this->getSetStatements($attr) . '
            WHERE user_id=:id;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        try {
            $stmt->execute($attr + ["id" => $id]);
        }
        //catch exceptions that are caused by invalid arguments
        catch (PDOException $e) {

            // Email duplicate
            if (
                str_contains($e->getMessage(), "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry") and
                str_contains($e->getMessage(), "for key 'user.email'")
            ) {
                throw new InvalidArgumentException("There is already a user with email: " . $attr["email"], 0, $e);
            }

            //no role with roleID
            else if (
                str_contains($e->getMessage(), "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`api_db_test`.`user`, CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`))")
            ) {
                throw new InvalidArgumentException("There is no role with roleID: " . $attr["roleID"], 0, $e);
            }

            //everything else
            else {
                throw $e;
            }
        }

        if ($stmt->rowCount() === 0) throw new InvalidArgumentException("No user with id: " . $id . " found.");
    }

    public function findByEmail(string $email): ?int
    {
        $stmt = $this->pdo->prepare('
            SELECT user_id
            FROM user
            WHERE email=:email;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        $stmt->execute(["email" => $email]);

        $response =  $stmt->fetchAll();

        if (sizeof($response) === 0) return null;

        return $response[0]["user_id"];
    }

    public function get(int $id): array
    {
        $stmt = $this->pdo->prepare('
            SELECT email, name, postcode, city, phone, role_id, hashed_pass, verified, verification_code, created_at, updated_at
            FROM user 
            WHERE user_id=:id;
        ');

        if (is_null($stmt)) throw new RuntimeException("pdo->prepare delivered null");

        $stmt->execute(["id" => $id]);

        $response = $stmt->fetchAll();

        if (sizeof($response) === 0) throw new InvalidArgumentException("There is no request with id: " . $id);

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
     * @param  array  $attr The attribute array
     * @return string The SET statements
     */
    private function getSetStatements(array $attr): string
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
        foreach ($attr as $key => $value) {
            $setStmts .= $getSetClause[$key] . ", ";
        }

        //remove the last ", "
        return substr($setStmts, 0, -2);
    }

    /**
     * Checks if the attribute array is valid
     *
     * @param  array $attr
     * 
     * @throws InvalidArgumentException if the array is empty, at least one key is not supported or at least one value has the wrong type
     */
    private function checkAttrArray(array $attr)
    {
        $validKeys = ["email", "name", "postcode", "city", "phone", "roleID", "hashedPass", "verified", "verificationCode"];
        $validTypes = [
            "email" => "string",
            "name" => "string",
            "postcode" => "string",
            "city" => "string",
            "phone" => "string",
            "roleID" => "int",
            "hashedPass" => "string",
            "verified" => "bool",
            "verificationCode" => "string"
        ];

        //throws exception if array is empty
        if (sizeof($attr) === 0) throw new InvalidArgumentException("The attribute array is empty.");

        //check each key-value pair
        foreach ($attr as $key => $value) {

            //throws exception if key is not valid
            if (!in_array($key, $validKeys)) throw new InvalidArgumentException("The attribute key: " . $key . " is not a valid key.");

            //throws exception if value type is not correct
            if (
                ($validTypes[$key] === "string" and !is_string($value)) or
                ($validTypes[$key] === "int" and !is_int($value)) or
                ($validTypes[$key] === "bool" and !is_bool($value))
            ) throw new InvalidArgumentException("The value of attribute " . $key . " need to be a " . $validTypes[$key]);
        }
    }
}
