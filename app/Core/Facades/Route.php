<?php

namespace App\Core\Facades;

/**
 * @method static void get(string $path = '/', \App\Core\Controllers\Controller|string|array $controller = null)
 * @method static void post(string $path = '/', \App\Core\Controllers\Controller|string|array $controller = null)
 * @method static void put(string $path = '/', \App\Core\Controllers\Controller|string|array $controller = null)
 * @method static void patch(string $path = '/', \App\Core\Controllers\Controller|string|array $controller = null)
 * @method static void delete(string $path = '/', \App\Core\Controllers\Controller|string|array $controller = null)
 * @method static void handle(string $method = 'GET', string $path = '/', \App\Core\Controllers\Controller|string|array $controller = null, array $data = [])
 * @method static \App\Core\Router\Router controller(\App\Core\Controllers\Controller|string $controller)
 * @method static \App\Core\Router\Router middleware(\App\Core\Contracts\Middleware\Middleware|string $middleware)
 * @method static void group(callable $group_route)
 * @method static void redirect(string $url, int $status_code = 303, array $data = [])
 * @method static void back()
 * 
 * @see \App\Core\Router\Router
 */
class Route extends Facade
{   
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'route';
    }
}