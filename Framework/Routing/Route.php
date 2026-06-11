<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;

final class Route
{
    public readonly string $pattern;

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array|string|Closure $action,
        private readonly array $middlewares = [],
    ) {
        $this->pattern =
            $this->compilePattern();
    }

    private function compilePattern(): string
    {
        $path =
            rtrim(
                $this->path,
                '/',
            );

        if ($path === '') {
            $path = '/';
        }

        $pattern =
            preg_replace_callback(
                '#\{([a-zA-Z0-9_]+)(?::([a-zA-Z]+))?\}#',
                static function (
                    array $matches,
                ): string {

                    return match (
                        $matches[2] ?? 'string'
                    ) {
                        'int' => '([0-9]+)',

                        default => '([^/]+)',
                    };
                },
                $path,
            );

        return '#^'
            . $pattern
            . '/?$#';
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getAction(): array|string|Closure
    {
        return $this->action;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}