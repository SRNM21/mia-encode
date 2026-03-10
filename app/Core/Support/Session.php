<?php

namespace App\Core\Support;

/**
 * Class Session
 *
 * Static wrapper for PHP native session handling.
 *
 * Provides safe and structured access to session storage.
 *
 * @package App\Core\Support
 */
class Session
{
    /**
     * Indicates whether the session has been started.
     *
     * @var bool
     */
    protected static bool $started = false;

    /**
     * Start the session if not already active.
     *
     * @throws \RuntimeException
     * @return bool
     */
    public static function start(): bool
    {
        if (!self::isStarted()) 
        {
            if (!session_start()) 
            {
                throw new \RuntimeException('Failed to start the session.');
            }

            self::$started = true;

            // Store previous URL if available
            if (isset($_SERVER['HTTP_REFERER'])) 
            {
                self::set('previous_url', $_SERVER['HTTP_REFERER']);
            }
        }

        return true;
    }

    /**
     * Store a value in session.
     *
     * @param string $key Session key identifier.
     * @param mixed $value Value to store.
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve a value from session.
     *
     * @param string $key Session key identifier.
     * @param mixed|null $default Default value if key does not exist.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove a specific key from session.
     *
     * @param string $key
     * @return void
     */
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Dump current session contents (debugging only).
     *
     * @return void
     */
    public static function dump(): void
    {
        print_r($_SESSION);
    }

    /**
     * Determine whether session is currently active.
     *
     * @return bool
     */
    public static function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE && self::$started;
    }

    /**
     * Retrieve entire session array.
     *
     * @return array
     */
    public static function getSession(): array
    {
        return $_SESSION;
    }

    /**
     * Clear all session variables.
     *
     * @return void
     */
    public static function clear(): void
    {
        session_unset();
    }

    /**
     * Destroy the session completely.
     *
     * @return void
     */
    public static function destroy(): void
    {
        session_destroy();
    }
}
