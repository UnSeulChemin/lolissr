<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\ErrorController;
use RuntimeException;

class Router
{
    /**
     * Liste des routes enregistrées.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $routes = [];

    /**
     * Enregistre une route GET.
     */
    public function get(string $path, string $action): void
    {
        $this->addRoute('GET', $path, $action);
    }

    /**
     * Enregistre une route POST.
     */
    public function post(string $path, string $action): void
    {
        $this->addRoute('POST', $path, $action);
    }

    /**
     * Retourne les routes enregistrées.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Enregistre une route.
     */
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

        if ($path === '/')
        {
            $pattern = '#^/$#';
        }
        else
        {
            $pattern = '#^' . rtrim((string) $pattern, '/') . '/?$#';
        }

        $this->routes[$method][] = [
            'path' => $path,
            'action' => $action,
            'pattern' => $pattern,
            'params' => $paramNames,
        ];
    }

    /**
     * Lance le dispatch de la requête.
     */
    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '')
        {
            $path = '/';
        }

        $path = $this->stripBasePath($path);
        $path = $this->normalizeRequestPath($path);
        $method = strtoupper($method);

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

                $this->callAction((string) $route['action'], $params);
                return;
            }
        }

        $allowedMethods = $this->findAllowedMethods($path);

        if ($allowedMethods !== [])
        {
            header('Allow: ' . implode(', ', $allowedMethods));

            $controller = new ErrorController();
            $controller->methodNotAllowed('Méthode non autorisée');
            return;
        }

        $controller = new ErrorController();
        $controller->notFound('Page introuvable');
    }

    /**
     * Retourne les méthodes autorisées pour une route trouvée.
     *
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
     * Appelle le controller et la méthode.
     *
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

    /**
     * Normalise un chemin de route déclaré.
     */
    private function normalizeRoutePath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/')
        {
            return '/';
        }

        return '/' . trim($path, '/');
    }

    /**
     * Normalise le chemin de la requête.
     */
    private function normalizeRequestPath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/')
        {
            return '/';
        }

        return '/' . trim($path, '/');
    }

    /**
     * Supprime le base path de l'URI courante.
     */
    private function stripBasePath(string $path): string
    {
        $basePath = Functions::basePath();

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