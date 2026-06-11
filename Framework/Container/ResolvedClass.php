<?php

declare(strict_types=1);

namespace Framework\Container;

final readonly class ResolvedClass
{
    /**
     * @param list<string> $dependencies
     */
    public function __construct(
        public array $dependencies,
        public bool $instantiable,
    ) {
    }
}