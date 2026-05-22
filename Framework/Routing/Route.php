<?php
declare(strict_types=1);

namespace Framework\Routing;

use Closure;

final class Route
{
    public string $pattern;

    public function __construct(
        private string $method,
        private string $path,
        private array|string|Closure $action,
        private array $middlewares = []
    ) {
        // Générer le pattern regex pour routes dynamiques avec slash final optionnel
        $this->pattern = '#^' . preg_replace_callback(
            '#\{([a-zA-Z0-9_]+)(?::([a-zA-Z]+))?\}#',
            function($matches) {
                return match($matches[2] ?? 'string') {
                    'int' => '([0-9]+)',
                    default => '([^/]+)',
                };
            },
            rtrim($this->path, '/')
        ) . '/?$#';
    }

    public function getMethod(): string { return $this->method; }
    public function getPath(): string { return $this->path; }
    public function getAction(): array|string|Closure { return $this->action; }
    public function getMiddlewares(): array { return $this->middlewares; }
}