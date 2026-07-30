<?php

declare(strict_types=1);

namespace Framework\Container;

use Framework\Debug\Profiler;

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
     * @var array<class-string, ReflectionClass<object>>
     */
    private array $reflections = [];

    private int $resolutionDepth = 0;

    public function __construct()
    {
        $this->instances[self::class] = $this;
    }

    // =========================================
    // ENREGISTREMENT
    // =========================================

    public function bind(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'singleton' => false
        ];

        unset($this->instances[$abstract]);
    }

    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'singleton' => true
        ];

        unset($this->instances[$abstract]);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;

        unset($this->bindings[$abstract]);
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract]))
        {
            return $this->instances[$abstract];
        }

        if (isset($this->resolving[$abstract]))
        {
            throw new RuntimeException(
                "Circular dependency detected while resolving: {$abstract}"
            );
        }

        $isRootResolution = $this->resolutionDepth === 0;

        if ($isRootResolution)
        {
            Profiler::start('container.resolve');
        }

        $this->resolving[$abstract] = true;
        $this->resolutionDepth++;

        try
        {
            $binding = $this->bindings[$abstract] ?? [
                'concrete' => $abstract,
                'singleton' => false
            ];

            $object = $this->resolve($binding['concrete']);

            if ($binding['singleton'])
            {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }
        finally
        {
            unset($this->resolving[$abstract]);

            $this->resolutionDepth--;

            if ($isRootResolution)
            {
                Profiler::end('container.resolve');
            }
        }
    }

    private function resolve(callable|string $concrete): object
    {
        if (is_callable($concrete))
        {
            $object = $concrete($this);

            if (! is_object($object))
            {
                throw new RuntimeException(
                    'Container factory must return an object.'
                );
            }

            return $object;
        }

        if (interface_exists($concrete))
        {
            throw new RuntimeException(
                "No binding registered for interface: {$concrete}"
            );
        }

        if (! class_exists($concrete))
        {
            throw new RuntimeException(
                "Class not found: {$concrete}"
            );
        }

        /** @var class-string $concrete */
        $reflection = $this->reflection($concrete);

        if (! $reflection->isInstantiable())
        {
            throw new RuntimeException(
                "Class is not instantiable: {$concrete}"
            );
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

                throw new RuntimeException(
                    sprintf(
                        'Unable to resolve %s::$%s',
                        $concrete,
                        $parameter->getName()
                    )
                );
            }

            $dependency = $type->getName();

            if ($type->allowsNull() && ! $this->canResolve($dependency))
            {
                $dependencies[] = null;

                continue;
            }

            $dependencies[] = $this->get($dependency);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    // =========================================
    // RÉFLEXION
    // =========================================

    /**
     * @param class-string $class
     *
     * @return ReflectionClass<object>
     */
    private function reflection(string $class): ReflectionClass
    {
        return $this->reflections[$class] ??= new ReflectionClass($class);
    }

    private function canResolve(string $abstract): bool
    {
        if (isset($this->instances[$abstract]) || isset($this->bindings[$abstract]))
        {
            return true;
        }

        if (interface_exists($abstract) || ! class_exists($abstract))
        {
            return false;
        }

        /** @var class-string $abstract */
        return $this->reflection($abstract)->isInstantiable();
    }
}