<?php

namespace App\Core\Facades;

/**
 * 
 * @method static \App\Core\Auth\Authenticable user()
 * @method static \App\Core\Auth\Authenticable check()
 * @method static \App\Core\Auth\Authenticable login()
 * @method static \App\Core\Auth\Authenticable logout()
 * @method static \App\Core\Auth\Authenticable attempt(array $credentials)
 * @method static bool isConnected()
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