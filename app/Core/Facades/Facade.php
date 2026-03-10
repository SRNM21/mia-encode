<?php

namespace App\Core\Facades;

use App\Core\Container\Container;

abstract class Facade
{ 
    /**
     * Resolved facade instances.
     *
     * @var array
     */
    protected static array $instances = [];

    /**
     * Shared container instance.
     * 
     * @var Container
     */
    protected static ?Container $container = null;

    /**
     * Inject container
     */
    public static function setContainer(Container $container): void
    {
        static::$container = $container;
    }

    /**
     * Returns the registered facade.
     *
     * @return array
     */
    public static function getFacadeMap()
    {
        return [
            'auth' => \App\Core\Auth\Authenticable::class,
            'db' => \App\Core\Database\Database::class,
            'route' => \App\Core\Router\Router::class,
        ];
    }

    /**
     * Gets the instance of the registered facade.
     *
     * @return \App\Core\Facades\Facade
     */
    public static function getInstance()
    {
        if (!static::$container)
        {
            throw new \RuntimeException('Container has not been set on Facade.');
        }

        $accessor = static::getFacadeAccessor();
        $map = static::getFacadeMap();

        if (!isset($map[$accessor])) 
        {
            throw new \RuntimeException(
                sprintf('Invalid facade accessor "%s".', $accessor)
            );
        }

        // Return cached resolved instance
        if (isset(static::$instances[$accessor])) 
        {
            return static::$instances[$accessor];
        }

        $concrete = $map[$accessor];

        // Resolve via container instead of new $class()
        $instance = static::$container->make($concrete);

        static::$instances[$accessor] = $instance;

        return $instance;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * 
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();

        if (!method_exists($instance, $method))
        {
            throw new \BadMethodCallException(
                sprintf(
                    'Method %s::%s does not exist.',
                    get_class($instance),
                    $method
                )
            );
        }

        return $instance->$method(...$args);
    }
}