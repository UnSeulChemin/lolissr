<?php

declare(strict_types=1);

namespace Framework\Container;

use Framework\Debug\Profiler;

use ReflectionClass;
use RuntimeException;

final class Container
{
    /**
     * Les liaisons explicites sont des instances uniques ; les autres classes sont résolues à chaque appel.
     * @var array<string, callable|string>
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

    /** @var array<class-string, list<ParameterPlan>> */
    private array $dependencyPlans = [];

    public function __construct()
    {
        $this->instances[self::class] = $this;
    }

    // =================================================
    // ENREGISTREMENT
    // =================================================

    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;

        unset($this->instances[$abstract]);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->assertCompatible($abstract, $instance);
        $this->instances[$abstract] = $instance;

        unset($this->bindings[$abstract]);
    }

    // =================================================
    // RÉSOLUTION
    // =================================================

    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract]))
        {
            return $this->instances[$abstract];
        }

        if (isset($this->resolving[$abstract]))
        {
            throw new ContainerResolutionException(
                'Circular dependency: ' . implode(' -> ', [...array_keys($this->resolving), $abstract])
            );
        }

        $isRootResolution = $this->resolving === [];

        if ($isRootResolution)
        {
            Profiler::start('container.resolve');
        }

        $this->resolving[$abstract] = true;

        try
        {
            $isSingleton = isset($this->bindings[$abstract]);

            $object = $this->resolve($this->bindings[$abstract] ?? $abstract);
            $this->assertCompatible($abstract, $object);

            if ($isSingleton)
            {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }
        catch (RuntimeException $exception)
        {
            if ($exception instanceof ContainerResolutionException)
            {
                throw $exception;
            }

            throw new ContainerResolutionException(
                $exception->getMessage() . ' [resolution: '
                    . implode(' -> ', array_keys($this->resolving)) . ']',
                previous: $exception
            );
        }
        finally
        {
            unset($this->resolving[$abstract]);

            if ($isRootResolution)
            {
                Profiler::end('container.resolve');
            }
        }
    }

    private function assertCompatible(string $abstract, object $object): void
    {
        // Les alias de services arbitraires restent valides ; appliquer uniquement les types PHP déclarés.
        if ((class_exists($abstract) || interface_exists($abstract)) && ! $object instanceof $abstract)
        {
            throw new RuntimeException(sprintf(
                'Service %s requires an instance of %s; %s returned.',
                $abstract,
                $abstract,
                get_debug_type($object)
            ));
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

        foreach ($this->dependencyPlan($concrete, $reflection) as $parameter)
        {
            if ($parameter->dependency === null)
            {
                if ($parameter->hasDefault)
                {
                    $dependencies[] = $parameter->defaultValue();

                    continue;
                }

                throw new RuntimeException(
                    sprintf(
                        'Unable to resolve %s::$%s',
                        $concrete,
                        $parameter->name
                    )
                );
            }

            $dependency = $parameter->dependency;

            if ($parameter->nullable && ! $this->canResolve($dependency))
            {
                $dependencies[] = null;

                continue;
            }

            $dependencies[] = $this->get($dependency);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * @param class-string $class
     * @param ReflectionClass<object> $reflection
     * @return list<ParameterPlan>
     */
    private function dependencyPlan(string $class, ReflectionClass $reflection): array
    {
        if (isset($this->dependencyPlans[$class])) return $this->dependencyPlans[$class];
        $constructor = $reflection->getConstructor();
        return $this->dependencyPlans[$class] = $constructor !== null ? ParameterPlan::forMethod($constructor) : [];
    }

    // =================================================
    // RÉFLEXION
    // =================================================

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
