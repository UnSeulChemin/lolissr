<?php

declare(strict_types=1);

namespace Framework\Routing;

use RuntimeException;

final class RouteCollection
{
    /**
     * @var array<string, int>
     */
    private array $routes = [];

    /**
     * @var array<string, int>
     */
    private array $routeCounts = [];

    /** @var array<string, array<string, array{route: Route, position: int}>> */
    private array $staticRoutes = [];

    /** @var array<string, array<string, array<int, Route>>> */
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

        $position = count($this->routes);
        $this->routes[$key] = $position;
        $method = $route->getMethod();
        if (str_contains($route->getPath(), '{'))
        {
            $segment = explode('/', trim($route->getPath(), '/'), 2)[0];
            $bucket = str_contains($segment, '{') ? '' : $segment;
            $this->dynamicRoutes[$method][$bucket][$position] = $route;
        }
        else
        {
            $path = '/' . trim($route->getPath(), '/');
            $this->staticRoutes[$method][$path] ??= ['route' => $route, 'position' => $position];
        }
        $this->routeCounts[$method] = ($this->routeCounts[$method] ?? 0) + 1;
    }

    // =========================================
    // LECTURE
    // =========================================

    /** @return list<Route> */
    public function candidates(string $method, string $uri): array
    {
        $path = trim($uri, '/');
        $static = $this->staticRoutes[$method]['/' . $path] ?? null;
        $segment = explode('/', $path, 2)[0];
        $dynamic = $this->dynamicRoutes[$method][$segment] ?? [];
        $fallback = $this->dynamicRoutes[$method][''] ?? [];
        // Each bucket already follows declaration order. Only mixed buckets need sorting.
        if ($dynamic === [])
        {
            $dynamic = $fallback;
        }
        elseif ($segment !== '' && $fallback !== [])
        {
            $dynamic += $fallback;
            ksort($dynamic);
        }
        $routes = [];
        foreach ($dynamic as $position => $route)
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

        foreach (array_keys($this->routeCounts) as $method)
        {
            foreach ($this->candidates($method, $uri) as $route)
            {
                if (preg_match($route->pattern, $uri) === 1)
                {
                    $methods[$method] = $this->routes[$method . ':' . $route->getPath()];
                    break;
                }
            }
        }
        asort($methods);
        return array_keys($methods);
    }
}
