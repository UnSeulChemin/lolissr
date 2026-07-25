<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;

final class Route
{
    private const PARAM_PATTERNS = ['int' => '[0-9]+'];

    public readonly string $pattern;

    /**
     * @var array{class-string, string}|string|Closure
     */
    private readonly array|string|Closure $action;

    /**
     * @param array{class-string, string}|string|Closure $action
     * @param list<class-string> $middlewares
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        array|string|Closure $action,
        private readonly array $middlewares = [],
    ) {
        $this->action = $action;
        $this->pattern = $this->compilePattern();
    }

    // =========================================
    // ROUTE
    // =========================================

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array{class-string, string}|string|Closure
     */
    public function getAction(): array|string|Closure
    {
        return $this->action;
    }

    /**
     * @return list<class-string>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    // =========================================
    // UTILITAIRES
    // =========================================

    private function compilePattern(): string
    {
        $path = rtrim($this->path, '/');

        if ($path === '')
        {
            $path = '/';
        }

        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([a-zA-Z]+))?\}#',
            static function (array $matches): string
            {
                $name = $matches[1];
                $type = $matches[2] ?? '';
                $pattern = self::PARAM_PATTERNS[$type] ?? '[^/]+';

                return "(?P<{$name}>{$pattern})";
            },
            $path,
        );

        return '#^' . $pattern . '/?$#';
    }
}