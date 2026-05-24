<?php

declare(strict_types=1);

namespace Framework\Container;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;
use Throwable;

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

    public function bind(
        string $abstract,
        callable|string|null $concrete = null,
    ): void {

        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'singleton' => false,
        ];
    }

    public function singleton(
        string $abstract,
        callable|string|null $concrete = null,
    ): void {

        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'singleton' => true,
        ];
    }

    public function get(
        string $abstract,
    ): object {

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding =
            $this->bindings[$abstract]
            ?? [
                'concrete' => $abstract,
                'singleton' => false,
            ];

        $object =
            $this->resolve(
                $binding['concrete'],
            );

        if ($binding['singleton']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function resolve(
        callable|string $concrete,
    ): object {

        // Factory callable
        if (is_callable($concrete)) {

            $object =
                $concrete($this);

            if (! is_object($object)) {
                throw new RuntimeException(
                    'Le container doit retourner un objet.',
                );
            }

            return $object;
        }

        // Missing class
        if (! class_exists($concrete)) {
            throw new RuntimeException(
                "Classe introuvable : {$concrete}",
            );
        }

        // Circular dependency detection
        if (isset($this->resolving[$concrete])) {
            throw new RuntimeException(
                "Dépendance circulaire détectée : {$concrete}",
            );
        }

        $this->resolving[$concrete] = true;

        try {

            // Reflection cache
            $reflection =
                $this->reflections[$concrete]
                ??= new ReflectionClass(
                    $concrete,
                );

            // Non instantiable
            if (! $reflection->isInstantiable()) {
                throw new RuntimeException(
                    "Classe non instanciable : {$concrete}",
                );
            }

            $constructor =
                $reflection->getConstructor();

            // No constructor
            if ($constructor === null) {
                return $reflection->newInstance();
            }

            $dependencies = [];

            foreach (
                $constructor->getParameters()
                as $parameter
            ) {

                $type =
                    $parameter->getType();

                // Primitive / unsupported type
                if (
                    ! $type instanceof ReflectionNamedType
                    || $type->isBuiltin()
                ) {

                    if (
                        $parameter->isDefaultValueAvailable()
                    ) {
                        $dependencies[] =
                            $parameter->getDefaultValue();

                        continue;
                    }

                    throw new RuntimeException(
                        sprintf(
                            'Impossible de résoudre %s::$%s',
                            $concrete,
                            $parameter->getName(),
                        ),
                    );
                }

                $dependencies[] =
                    $this->get(
                        $type->getName(),
                    );
            }

            return $reflection->newInstanceArgs(
                $dependencies,
            );

        } catch (Throwable $exception) {

            throw $exception;

        } finally {

            unset(
                $this->resolving[$concrete],
            );
        }
    }
}