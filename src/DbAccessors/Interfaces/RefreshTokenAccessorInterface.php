<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace DbAccessors\Interfaces;

/**
 * Accessor for the refreshToken database table
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
     * Increase the value of 'count' for the specified use by 1
     *
     * @param  int   $userID        The users id.
     * 
     * @throws DBexception        if there is a problem with the database.
     *      (UserNotFoundException | ...)   if there is no user with this id.
     */
    public function increaseCount(int $userID): void;
}
