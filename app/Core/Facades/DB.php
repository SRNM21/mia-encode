<?php

namespace App\Core\Facades;

/**
 * @method static \App\Core\Database\Database|void initializeDB()
 * @method static \App\Core\Database\Database setPDO(PDO $pdo)
 * @method static \PDO getPDO()
 * @method static bool isConnected()
 * 
 * @see \App\Core\Database\Database
 */
class DB extends Facade
{   
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}