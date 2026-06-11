<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;

final class Route
{
    public readonly string $pattern;

    private const PARAM_PATTERNS = [
        'int' => '([0-9]+)',
    ];

    /**
     * @param list<string> $middlewares
     */
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
            ) ?: '/';

        $pattern =
            preg_replace_callback(
                '#\{([a-zA-Z0-9_]+)(?::([a-zA-Z]+))?\}#',
                static function (
                    array $matches,
                ): string {

                    return self::PARAM_PATTERNS[
                        $matches[2] ?? ''
                    ] ?? '([^/]+)';
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

    /**
     * @return array{0: class-string, 1: string}|string|Closure
     */
    public function getAction(): array|string|Closure
    {
        return $this->action;
    }

    /**
     * @return list<string>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}