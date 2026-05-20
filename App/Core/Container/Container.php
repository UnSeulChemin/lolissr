<?php

declare(strict_types=1);

namespace App\Core\Container;

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

    public function bind(
        string $abstract,
        callable|string|null $concrete = null
    ): void {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'singleton' => false,
        ];
    }

    public function singleton(
        string $abstract,
        callable|string|null $concrete = null
    ): void {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'singleton' => true,
        ];
    }

    public function get(
        string $abstract
    ): object {
        if (isset($this->instances[$abstract]))
        {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract]
            ?? [
                'concrete' => $abstract,
                'singleton' => false,
            ];

        $object = $this->resolve(
            $binding['concrete']
        );

        if ($binding['singleton'])
        {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function resolve(
        callable|string $concrete
    ): object {
        if (is_callable($concrete))
        {
            return $concrete($this);
        }

        if (!class_exists($concrete))
        {
            throw new RuntimeException(
                "Classe introuvable : {$concrete}"
            );
        }

        $reflection = new ReflectionClass(
            $concrete
        );

        if (!$reflection->isInstantiable())
        {
            throw new RuntimeException(
                "Classe non instanciable : {$concrete}"
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null)
        {
            return new $concrete();
        }

        $dependencies = [];

        foreach (
            $constructor->getParameters()
            as $parameter
        ) {
            $type = $parameter->getType();

            if (
                !$type instanceof ReflectionNamedType
                || $type->isBuiltin()
            ) {
                if ($parameter->isDefaultValueAvailable())
                {
                    $dependencies[] =
                        $parameter->getDefaultValue();

                    continue;
                }

                throw new RuntimeException(
                    "Impossible de résoudre {$concrete}::\${$parameter->getName()}"
                );
            }

            $dependencies[] = $this->get(
                $type->getName()
            );
        }

        return $reflection->newInstanceArgs(
            $dependencies
        );
    }
}