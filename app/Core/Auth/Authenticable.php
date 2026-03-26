<?php

namespace App\Core\Auth;

use App\Core\Contracts\Auth\Authenticable as AuthAuthenticableContract;
use App\Core\Facades\Auth;
use App\Core\Facades\DB;
use App\Core\Support\Session;
use App\Models\User;
use Exception;

/**
 * Class Authenticable
 *
 * Session-based authentication handler.
 *
 * This class manages authentication state using PHP sessions.
 * It stores only the authenticated user's identifier in session storage.
 *
 * @package App\Core\Auth
 */
class Authenticable implements AuthAuthenticableContract
{
    /**
     * Session key used to store authenticated user ID.
     *
     * @var string
     */
    private string $sessionKey = 'auth_user_id';

    /**
     * Cached authenticated user instance.
     *
     * This prevents repeated database queries during a single request lifecycle.
     *
     * @var User|null
     */
    private ?User $user = null;

    /**
     * Attempt to authenticate a user using credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function attempt(array $credentials): bool
    {
        if (
            !isset($credentials['username']) ||
            !isset($credentials['password'])
        ) {
            return false;
        }

        $username = $credentials['username'];
        $plainPassword = $credentials['password'];
        
        /** @var User|null $user */
        $user = User::where('username', '=', $username)->first();

        if (!$user) 
        {
            return false;
        }

        if (!$user->checkPassword($plainPassword)) 
        {
            return false;
        }

        // Authentication successful
        $this->login($user);

        return true;
    }

    /**
     * Returns the currently authenticated user model instance.
     *
     * If not already loaded, it attempts to retrieve the user
     * from the session-stored user ID.
     *
     * @return User|null
     */
    public function user(): ?User
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = Session::get($this->sessionKey);

        if (!$userId) {
            return null;
        }

        $this->user = User::get($userId);

        return $this->user;
    }

    /**
     * Determine if a user is currently authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return Session::get($this->sessionKey) !== null;
    }

    /**
     * Return true if the user is an `ADMIN`
     *
     * @return boolean
     */
    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user->isAdmin();
    }

    /**
     * Return true if the user is an `ENCODER`
     *
     * @return boolean
     */
    public function isEncoder(): bool
    {
        $user = $this->user();
        return $user->isEncoder();
    }

    /**
     * Attempts to change the current authenticated user.
     *
     * @return boolean
     */
    public function changePassword(string $newPassword): bool 
    {
        $user_table = 'account_tbl';
        $password_column = 'pass';
        $where_column = 'id';
        $password_update_column = 'last_password_update';

        try
        {
            $user = Auth::user();

            // check authenthicated user on this session
            if (!$user) return false;

            $sql = "UPDATE {$user_table}
                SET {$password_column} = ?,
                    {$password_update_column} = ?
                WHERE {$where_column} = ?
                LIMIT 1";

            $bindings = [
                password_hash($newPassword, PASSWORD_DEFAULT), // SET password_column
                date('Y-m-d H:i:s'), // SET password_update_column
                $user->id // WHERE where_column
            ];

            $stm = DB::getPDO()->prepare($sql);

            foreach ($bindings as $index => $value) 
            {
                $stm->bindValue($index + 1, $value);
            }

            $stm->execute();

            return $stm->rowCount() > 0;
        }
        catch (Exception $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Log in a user by storing their ID in session.
     *
     * - Regenerates session ID (prevents session fixation attacks).
     * - Stores only the user identifier.
     *
     * @param User $user Authenticated user model instance.
     * @return void
     */
    public function login(User $user): void
    {
        Session::start();
        Session::set($this->sessionKey, $user->getAttribute('id'));
        session_regenerate_id(true);

        $this->user = $user;
    }

    /**
     * Log out the currently authenticated user.
     *
     * - Removes authentication session key.
     * - Clears cached user.
     * - Regenerates session ID.
     *
     * @return void
     */
    public function logout(): void
    {
        Session::start();
        Session::forget($this->sessionKey);

        $this->user = null;

        session_regenerate_id(true);
    }
}
