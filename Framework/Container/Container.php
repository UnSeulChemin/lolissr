<?php

declare(strict_types=1);

namespace Framework\Container;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    /**
     * @var array<string, array{
     *     concrete: callable|string,
     *     singleton: bool
     * }>
     */
    private array $bindings = [];

    /**
     * @var array<string, object>
     */
    private array $instances = [];

    /**
     * @var array<string, bool>
     */
    private array $resolving = [];

    /**
     * @var array<string, ReflectionClass<object>>
     */
    private array $reflections = [];

    // =========================================
    // CONTAINER
    // =========================================

    public function bind(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = ['concrete' => $concrete ?? $abstract, 'singleton' => false];
    }

    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = ['concrete' => $concrete ?? $abstract, 'singleton' => true];
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract]))
        {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? ['concrete' => $abstract, 'singleton' => false];

        $object = $this->resolve($binding['concrete']);

        if ($binding['singleton'])
        {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    private function resolve(callable|string $concrete): object
    {
        if (is_callable($concrete))
        {
            $object = $concrete($this);

            if (! is_object($object))
            {
                throw new RuntimeException('Container factory must return an object.');
            }

            return $object;
        }

        if (interface_exists($concrete))
        {
            throw new RuntimeException("No binding registered for interface: {$concrete}");
        }

        if (! class_exists($concrete))
        {
            throw new RuntimeException("Class not found: {$concrete}");
        }

        if (isset($this->resolving[$concrete]))
        {
            throw new RuntimeException("Circular dependency detected: {$concrete}");
        }

        $this->resolving[$concrete] = true;

        try
        {
            $reflection = $this->reflections[$concrete] ??= new ReflectionClass($concrete);

            if (! $reflection->isInstantiable())
            {
                throw new RuntimeException("Class is not instantiable: {$concrete}");
            }

            $constructor = $reflection->getConstructor();

            if ($constructor === null)
            {
                return $reflection->newInstance();
            }

            $dependencies = [];

            foreach ($constructor->getParameters() as $parameter)
            {
                $type = $parameter->getType();

                if (! $type instanceof ReflectionNamedType || $type->isBuiltin())
                {
                    if ($parameter->isDefaultValueAvailable())
                    {
                        $dependencies[] = $parameter->getDefaultValue();

                        continue;
                    }

                    throw new RuntimeException(sprintf('Unable to resolve %s::$%s', $concrete, $parameter->getName()));
                }

                $dependencies[] = $this->get($type->getName());
            }

            return $reflection->newInstanceArgs($dependencies);
        }
        finally
        {
            unset($this->resolving[$concrete]);
        }
    }
}
