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
     * @var array<string, string>
     */
    private array $parameters = [];

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

    /**
     * @param array<string, string> $matches
     *
     * @return array<string, string|int>
     */
    public function castParameters(array $matches): array
    {
        $parameters = [];

        foreach ($this->parameters as $name => $type)
        {
            if (! array_key_exists($name, $matches))
            {
                continue;
            }

            $value = rawurldecode($matches[$name]);

            $parameters[$name] = match ($type)
            {
                'int' => (int) $value,
                default => $value,
            };
        }

        return $parameters;
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
            function (array $matches): string
            {
                $name = $matches[1];
                $type = $matches[2] ?? 'string';

                $this->parameters[$name] = $type;

                $parameterPattern = self::PARAM_PATTERNS[$type] ?? '[^/]+';

                return "(?P<{$name}>{$parameterPattern})";
            },
            $path,
        );

        return '#^' . $pattern . '/?$#';
    }
}