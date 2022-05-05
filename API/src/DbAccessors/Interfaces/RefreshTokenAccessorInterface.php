<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces;

/**
 * Accessor for the "refreshToken" database table
 * 
 * Abstracts all SQL statements
 */
interface RefreshTokenAccessorInterface
{
    /**
     * Gets the value of 'count' for the specified user
     *
     * @param  int   $userID        The users id.
     * @return null|int             The count (or null if the user has no entry).
     * 
     * @throws DBexception    if there is a problem with the database.
     */
    public function getCountByUserID(int $userID): ?int;

    /**
     * Increase the value of 'count' for the specified use by 1 r
     *
     * @param  int   $userID        The users id.
     * 
     * @throws DBexception        if there is a problem with the database.
     */
    public function increaseCount(int $userID): void;
}
