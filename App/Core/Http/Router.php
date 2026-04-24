<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Exceptions\MethodNotAllowedException;
use App\Core\Exceptions\NotFoundException;
use RuntimeException;

class Router
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $routes = [];

    private ?string $lastMethod = null;
    private ?int $lastRouteIndex = null;

    public function get(string $path, string $action): self
    {
        $this->addRoute('GET', $path, $action);

        return $this;
    }

    public function post(string $path, string $action): self
    {
        $this->addRoute('POST', $path, $action);

        return $this;
    }

    /**
     * @param string|array<int, string> $middlewares
     */
    public function middleware(string|array $middlewares): self
    {
        if ($this->lastMethod === null || $this->lastRouteIndex === null)
        {
            throw new RuntimeException('Aucune route disponible pour ajouter un middleware.');
        }

        $middlewares = is_array($middlewares)
            ? $middlewares
            : [$middlewares];

        foreach ($middlewares as $middleware)
        {
            $this->routes[$this->lastMethod][$this->lastRouteIndex]['middlewares'][] = $middleware;
        }

        return $this;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function addRoute(string $method, string $path, string $action): void
    {
        $path = $this->normalizeRoutePath($path);
        $paramNames = [];

        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static function (array $matches) use (&$paramNames): string
            {
                $paramNames[] = $matches[1];

                return '([^/]+)';
            },
            $path
        );

        $pattern = $path === '/'
            ? '#^/$#'
            : '#^' . rtrim((string) $pattern, '/') . '/?$#';

        $this->routes[$method][] = [
            'path' => $path,
            'action' => $action,
            'pattern' => $pattern,
            'params' => $paramNames,
            'middlewares' => [],
        ];

        $this->lastMethod = $method;
        $this->lastRouteIndex = array_key_last($this->routes[$method]);
    }

    public function dispatch(?string $uri = null, ?string $method = null): void
    {
        $path = $uri ?? Request::path();
        $method = $method ?? Request::method();

        $path = $this->stripBasePath($path);
        $path = $this->normalizeRequestPath($path);
        $method = strtoupper(trim($method));

        foreach ($this->routes[$method] ?? [] as $route)
        {
            if (preg_match($route['pattern'], $path, $matches) === 1)
            {
                array_shift($matches);

                $params = [];

                foreach ($route['params'] as $index => $name)
                {
                    $params[$name] = $matches[$index] ?? null;
                }

                $this->runMiddlewares($route['middlewares'] ?? []);
                $this->callAction((string) $route['action'], $params);

                return;
            }
        }

        $allowedMethods = $this->findAllowedMethods($path);

        if ($allowedMethods !== [])
        {
            header('Allow: ' . implode(', ', $allowedMethods));
            throw new MethodNotAllowedException('Méthode non autorisée');
        }

        throw new NotFoundException('Page introuvable');
    }

    /**
     * @param array<int, string> $middlewares
     */
    private function runMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middlewareClass)
        {
            if (!class_exists($middlewareClass))
            {
                throw new RuntimeException('Middleware introuvable : ' . $middlewareClass);
            }

            $middleware = new $middlewareClass();

            if (!method_exists($middleware, 'handle'))
            {
                throw new RuntimeException('Méthode handle() introuvable dans : ' . $middlewareClass);
            }

            $middleware->handle();
        }
    }

    /**
     * @return string[]
     */
    private function findAllowedMethods(string $path): array
    {
        $allowedMethods = [];

        foreach ($this->routes as $registeredMethod => $registeredRoutes)
        {
            foreach ($registeredRoutes as $route)
            {
                if (preg_match($route['pattern'], $path) === 1)
                {
                    $allowedMethods[] = $registeredMethod;
                }
            }
        }

        return array_values(array_unique($allowedMethods));
    }

    /**
     * @param array<string, mixed> $params
     */
    private function callAction(string $action, array $params = []): void
    {
        if (!str_contains($action, '@'))
        {
            throw new RuntimeException('Action invalide : ' . $action);
        }

        [$controllerName, $method] = explode('@', $action, 2);

        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass))
        {
            throw new RuntimeException('Controller introuvable : ' . $controllerClass);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method))
        {
            throw new RuntimeException(
                'Méthode introuvable : ' . $controllerClass . '::' . $method
            );
        }

        $controller->{$method}(...array_values($params));
    }

    private function normalizeRoutePath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/')
        {
            return '/';
        }

        return '/' . trim($path, '/');
    }

    private function normalizeRequestPath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/')
        {
            return '/';
        }

        return '/' . trim($path, '/');
    }

    private function stripBasePath(string $path): string
    {
        $basePath = base_path();

        if ($basePath === '/')
        {
            return $path;
        }

        $trimmedBasePath = rtrim($basePath, '/');

        if ($path === $trimmedBasePath)
        {
            return '/';
        }

        if (str_starts_with($path, $trimmedBasePath . '/'))
        {
            $path = substr($path, strlen($trimmedBasePath));

            return $path === '' ? '/' : $path;
        }

        return $path;
    }
}