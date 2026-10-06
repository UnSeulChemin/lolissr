<?php

declare(strict_types=1);

namespace Framework\Container;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

// Métadonnées communes des paramètres ; les valeurs par défaut sont évaluées à chaque appel.
final readonly class ParameterMetadata
{
    public string $name;
    public ?string $dependency;
    public bool $nullable;
    public bool $hasDefault;

    private function __construct(private ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        $this->name = $parameter->getName();
        $this->dependency = $type instanceof ReflectionNamedType && ! $type->isBuiltin()
            ? $type->getName() : null;
        $this->nullable = $type?->allowsNull() ?? false;
        $this->hasDefault = $parameter->isDefaultValueAvailable();
    }

    /** @return list<self> */
    public static function forMethod(ReflectionMethod $method): array
    {
        return array_map(static fn (ReflectionParameter $parameter): self => new self($parameter), $method->getParameters());
    }

    public function defaultValue(): mixed
    {
        return $this->hasDefault ? $this->parameter->getDefaultValue() : null;
    }
}
