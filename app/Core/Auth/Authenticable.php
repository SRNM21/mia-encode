<?php

namespace App\Core\Auth;

use App\Core\Contracts\Auth\Authenticable as AuthAuthenticableContract;
use App\Core\Support\Session;
use App\Models\User;

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
