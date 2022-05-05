<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ShouldNeverHappenException;

class MySqlRefreshTokenAccessor extends MySqlAccessor implements RefreshTokenAccessorInterface
{

    public function getCountByUserID(int $userID): ?int
    {
        $sql = 'SELECT count
                FROM refreshToken
                WHERE user_id=:userID;';

        $stmt = $this->prepareAndExecute($sql, ["userID" => $userID]);

        $response =  $stmt->fetchAll();

        //if no Request was found: return null
        if (sizeof($response) === 0) return null;

        //return the id
        return $response[0]["count"];
    }

    public function increaseCount(int $userID): void
    {
        //if no entry exist create one
        //else increase by one
        $sql = 'IF NOT EXISTS (SELECT * FROM refreshToken WHERE user_id = :id) THEN
                    INSERT INTO refreshToken (user_id)
                    VALUES (:id)
                ELSE
                    UPDATE refreshToken
                    SET count=count+1
                    WHERE user_id=:id
                END IF;';

        $stmt = $this->prepareAndExecute($sql, ["id" => $userID]);

        //if no user updated
        if ($stmt->rowCount() === 0) throw new ShouldNeverHappenException("Either a new entry was made or an existing was updated.");
    }
}
