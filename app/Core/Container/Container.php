<?php

namespace App\Core\Container;

use App\Http\Request\Request;
use ReflectionMethod;
use ReflectionClass;
use ReflectionNamedType;
use Exception;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    /**
     * Bind an abstract to a concrete.
     */
    public function bind(string $abstract, ?string $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    /**
     * Resolve a class from container.
     */
    public function make(string $abstract)
    {   
        // Return existing singleton instance
        if (isset($this->instances[$abstract])) 
        {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if (!class_exists($concrete)) 
        {
            throw new Exception("Cannot resolve {$abstract}");
        }

        $reflection = new ReflectionClass($concrete);
        
        if (!$reflection->isInstantiable()) 
        {
            throw new Exception("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) 
        {
            return new $concrete;
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $param) 
        {
            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) 
            {   
                throw new Exception("Unresolvable dependency '{$param->getName()}' of class {$constructor->getDeclaringClass()->getName()}.");
            }

            $dependencies[] = $this->make($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Call method with dependency injection.
     */
    public function call(array $callable, array $provided = [])
    {
        [$class, $method] = $callable;

        $reflection = new ReflectionMethod($class, $method);
        $dependencies = [];

        foreach ($reflection->getParameters() as $param) 
        {
            $type = $param->getType();

            // If parameter has a class type
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) 
            {
                $className = $type->getName();

                // If Request subclass → capture request
                if (is_subclass_of($className, Request::class) || $className === Request::class)
                {
                    $dependencies[] = $className::capture();
                    continue;
                }

                foreach ($provided as $key => $value) 
                {
                    if ($value instanceof $className) 
                    {
                        $dependencies[] = $value;
                        unset($provided[$key]);
                        continue 2;
                    }
                }

                $dependencies[] = $this->make($className);
            }
            else 
            {
                // Built-in types (string, int, array, etc.)
                if (count($provided)) 
                {
                    $dependencies[] = array_shift($provided);
                }
                else if ($param->isDefaultValueAvailable()) 
                {
                    $dependencies[] = $param->getDefaultValue();
                }
                else 
                {
                    throw new Exception(
                        "Unresolvable dependency '{$param->getName()}'"
                    );
                }
            }
        }

        return $reflection->invokeArgs($class, $dependencies);
    }
}