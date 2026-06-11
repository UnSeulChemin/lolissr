<?php

declare(strict_types=1);

namespace Framework\Routing;

use RuntimeException;

final class RouteCollection
{
    /**
     * @var array<string, Route>
     */
    private array $routes = [];

    /**
     * @var array<string, list<Route>>
     */
    private array $routesByMethod = [];

    public function add(
        Route $route,
    ): void {

        $key =
            $route->getMethod()
            . ':'
            . $route->getPath();

        if (isset($this->routes[$key]))
        {
            throw new RuntimeException(
                "Duplicate route detected: {$key}",
            );
        }

        $this->routes[$key] =
            $route;

        $this->routesByMethod[
            $route->getMethod()
        ][] = $route;
    }

    /**
     * @return array<string, Route>
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @return list<Route>
     */
    public function forMethod(
        string $method,
    ): array {
        return $this->routesByMethod[$method]
            ?? [];
    }

    /**
     * @return list<string>
     */
    public function list(): array
    {
        $routes = [];

        foreach (
            $this->routes
            as $route
        ) {

            $action =
                $route->getAction();

            $routes[] =
                sprintf(
                    '%s %s -> %s',
                    $route->getMethod(),
                    $route->getPath(),
                    is_string($action)
                        ? $action
                        : '[callable]',
                );
        }

        return $routes;
    }
}