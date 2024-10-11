<?php

namespace App\Traits;

use App\Models\User;

trait Users
{
    /**
     * Check whether an email address exists in the system.
     *
     * @param string $email
     * @return bool
     */
    public function emailAddressExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Check whether a username exists in the system.
     *
     * @param string $username
     * @return bool
     */
    public function usernameExists(string $username): bool
    {
        return User::where('username', $username)->exists();
    }

    /**
     * Check if the given email belongs to the specified user.
     *
     * @param int $userId
     * @param string $email
     * @return bool
     */
    public function emailBelongsToUser(int $userId, string $email): bool
    {
        return User::where('id', $userId)->where('email', $email)->exists();
    }

    /**
     * Check if the given username belongs to the specified user.
     *
     * @param int $userId
     * @param string $username
     * @return bool
     */
    public function usernameBelongsToUser(int $userId, string $username): bool
    {
        return User::where('id', $userId)->where('username', $username)->exists();
    }
}
