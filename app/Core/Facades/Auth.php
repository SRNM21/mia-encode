<?php

namespace App\Core\Facades;

/**
 * 
 * @method static \App\Core\Auth\Authenticable user()
 * @method static bool check()
 * @method static bool isAdmin()
 * @method static bool isEncoder()
 * @method static void login()
 * @method static void logout()
 * @method static bool attempt(array $credentials)
 * 
 * @see \App\Core\Auth\Authenticable
 */
class Auth extends Facade
{   
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth';
    }
} 