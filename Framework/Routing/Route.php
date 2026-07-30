<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;
use RuntimeException;

final class Route
{
    private const PARAM_PATTERNS = [
        'int' => '[0-9]+',
        'string' => '[^/]+'
    ];

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
        private readonly array $middlewares = []
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
                default => $value
            };
        }

        return $parameters;
    }

    // =========================================
    // COMPILATION
    // =========================================

    private function compilePattern(): string
    {
        $path = trim($this->path, '/');

        if ($path === '')
        {
            return '#^/?$#';
        }

        $segments = array_map(
            fn (string $segment): string => $this->compileSegment($segment),
            explode('/', $path)
        );

        return '#^/' . implode('/', $segments) . '/?$#';
    }

    private function compileSegment(string $segment): string
    {
        $matched = preg_match_all(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([a-zA-Z]+))?\}#',
            $segment,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        if ($matched === false)
        {
            throw new RuntimeException("Unable to compile route segment: {$segment}");
        }

        if ($matched === 0)
        {
            return preg_quote($segment, '#');
        }

        $pattern = '';
        $offset = 0;

        foreach ($matches as $match)
        {
            $placeholder = $match[0][0];
            $placeholderOffset = $match[0][1];
            $name = $match[1][0];
            $type = $match[2][0] ?? 'string';

            if (isset($this->parameters[$name]))
            {
                throw new RuntimeException("Duplicate route parameter: {$name}");
            }

            if (! isset(self::PARAM_PATTERNS[$type]))
            {
                throw new RuntimeException("Unsupported route parameter type: {$type}");
            }

            $staticPart = substr($segment, $offset, $placeholderOffset - $offset);

            $pattern .= preg_quote($staticPart, '#');
            $pattern .= "(?P<{$name}>" . self::PARAM_PATTERNS[$type] . ')';

            $this->parameters[$name] = $type;
            $offset = $placeholderOffset + strlen($placeholder);
        }

        $pattern .= preg_quote(substr($segment, $offset), '#');

        return $pattern;
    }
}