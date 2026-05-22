<?php
declare(strict_types=1);

namespace Framework\Routing;

final class RouteCollection
{
    /** @var Route[] */
    private array $routes = [];

    public function add(Route $route): void
    {
        $key = $route->getMethod() . ':' . $route->getPath();
        if (isset($this->routes[$key])) {
            throw new \RuntimeException("Duplicate route detected: {$key}");
        }
        $this->routes[$key] = $route;
    }

    public function all(): array { return $this->routes; }

    public function list(): array
    {
        $list = [];
        foreach ($this->routes as $route) {
            $list[] = sprintf(
                '%s %s -> %s',
                $route->getMethod(),
                $route->getPath(),
                is_string($route->getAction()) ? $route->getAction() : '[Controller::class, method]'
            );
        }
        return $list;
    }
}