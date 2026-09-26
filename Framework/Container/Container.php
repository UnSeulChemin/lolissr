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

    /** @var array<class-string, list<array{name: string, dependency: string|null, nullable: bool, hasDefault: bool, default: \Closure(): mixed}>> */
    private array $dependencyPlans = [];

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
        $this->assertCompatible($abstract, $instance);
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
            throw new ContainerResolutionException(
                'Circular dependency: ' . implode(' -> ', [...array_keys($this->resolving), $abstract])
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
            $this->assertCompatible($abstract, $object);

            if ($binding['singleton'])
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

            $this->resolutionDepth--;

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
            if ($parameter['dependency'] === null)
            {
                if ($parameter['hasDefault'])
                {
                    $dependencies[] = ($parameter['default'])();

                    continue;
                }

                throw new RuntimeException(
                    sprintf(
                        'Unable to resolve %s::$%s',
                        $concrete,
                        $parameter['name']
                    )
                );
            }

            $dependency = $parameter['dependency'];

            if ($parameter['nullable'] && ! $this->canResolve($dependency))
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
     * @return list<array{name: string, dependency: string|null, nullable: bool, hasDefault: bool, default: \Closure(): mixed}>
     */
    private function dependencyPlan(string $class, ReflectionClass $reflection): array
    {
        if (isset($this->dependencyPlans[$class])) return $this->dependencyPlans[$class];
        $plan = [];
        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter)
        {
            $type = $parameter->getType();
            $plan[] = [
                'name' => $parameter->getName(),
                'dependency' => $type instanceof ReflectionNamedType && ! $type->isBuiltin() ? $type->getName() : null,
                'nullable' => $type?->allowsNull() ?? false,
                'hasDefault' => $parameter->isDefaultValueAvailable(),
                'default' => static fn (): mixed => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            ];
        }
        return $this->dependencyPlans[$class] = $plan;
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