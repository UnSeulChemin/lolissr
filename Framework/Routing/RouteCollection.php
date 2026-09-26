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

    /** @var array<string, array<string, array{route: Route, position: int}>> */
    private array $staticRoutes = [];

    /** @var array<string, array<int, Route>> */
    private array $dynamicRoutes = [];

    // =========================================
    // ROUTES
    // =========================================

    public function add(Route $route): void
    {
        $key = $route->getMethod() . ':' . $route->getPath();

        if (isset($this->routes[$key]))
        {
            throw new RuntimeException("Duplicate route detected: {$key}");
        }

        $this->routes[$key] = $route;
        $method = $route->getMethod();
        $position = count($this->routesByMethod[$method] ?? []);
        if (str_contains($route->getPath(), '{'))
        {
            $this->dynamicRoutes[$method][$position] = $route;
        }
        else
        {
            $path = '/' . trim($route->getPath(), '/');
            $this->staticRoutes[$method][$path] ??= ['route' => $route, 'position' => $position];
        }
        $this->routesByMethod[$route->getMethod()][] = $route;
    }

    // =========================================
    // LECTURE
    // =========================================

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
    public function forMethod(string $method): array
    {
        return $this->routesByMethod[$method] ?? [];
    }

    /** @return list<Route> */
    public function candidates(string $method, string $uri): array
    {
        $static = $this->staticRoutes[$method]['/' . trim($uri, '/')] ?? null;
        $routes = [];
        foreach ($this->dynamicRoutes[$method] ?? [] as $position => $route)
        {
            if ($static !== null && $position > $static['position']) break;
            $routes[] = $route;
        }
        if ($static !== null) $routes[] = $static['route'];
        return $routes;
    }

    /**
     * @return list<string>
     */
    public function allowedMethodsFor(string $uri): array
    {
        $methods = [];

        foreach ($this->routes as $route)
        {
            if (preg_match($route->pattern, $uri) === 1)
            {
                $methods[] = $route->getMethod();
            }
        }

        return array_values(array_unique($methods));
    }

    /**
     * @return list<string>
     */
    public function list(): array
    {
        $routes = [];

        foreach ($this->routes as $route)
        {
            $action = $route->getAction();

            $routes[] = sprintf(
                '%s %s -> %s',
                $route->getMethod(),
                $route->getPath(),
                is_string($action) ? $action : '[callable]'
            );
        }

        return $routes;
    }
}
