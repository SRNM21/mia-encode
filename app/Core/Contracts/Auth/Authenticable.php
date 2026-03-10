<?php

namespace App\Core\Contracts\Auth;

use App\Models\User;

/**
 * Contract for authentication services.
 *
 * Defines the required behaviors for authentication mechanisms
 * without enforcing implementation details or internal state.
 */
interface Authenticable
{
    /**
     * Get the currently authenticated user.
     *
     * @return User|null Returns the authenticated user instance,
     *                    or null if no user is authenticated.
     */
    public function user(): ?User;

    /**
     * Determine whether a user is currently authenticated.
     *
     * @return bool True if authenticated, otherwise false.
     */
    public function check(): bool;

    /**
     * Authenticate the given user.
     *
     * @param User $user The user instance to authenticate.
     *
     * @return void
     */
    public function login(User $user): void;

    /**
     * Log out the currently authenticated user.
     *
     * @return void
     */
    public function logout(): void;
}
