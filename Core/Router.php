<?php

namespace App\Core;

class Router
{
    /**
     * Liste des routes enregistrées
     */
    private array $routes = [];

    /**
     * Enregistre une route GET
     */
    public function get(string $path, string $action): void
    {
        $this->addRoute('GET', $path, $action);
    }

    /**
     * Enregistre une route POST
     */
    public function post(string $path, string $action): void
    {
        $this->addRoute('POST', $path, $action);
    }

    /**
     * Enregistre une route
     */
    private function addRoute(string $method, string $path, string $action): void
    {
        $paramNames = [];

        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function ($matches) use (&$paramNames)
            {
                $paramNames[] = $matches[1];
                return '([^/]+)';
            },
            $path
        );

        $pattern = '#^' . rtrim($pattern, '/') . '/?$#';

        if ($path === '/')
        {
            $pattern = '#^/$#';
        }

        $this->routes[$method][] = [
            'path' => $path,
            'action' => $action,
            'pattern' => $pattern,
            'params' => $paramNames
        ];
    }

    /**
     * Lance le dispatch
     */
    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $basePath = rtrim(Functions::basePath(), '/');

        if ($basePath !== '' && $basePath !== '/')
        {
            if (str_starts_with($path, $basePath))
            {
                $path = substr($path, strlen($basePath));
            }
        }

        $path = $path === '' ? '/' : $path;
        $method = strtoupper($method);

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route)
        {
            if (preg_match($route['pattern'], $path, $matches))
            {
                array_shift($matches);

                $params = [];
                foreach ($route['params'] as $index => $name)
                {
                    $params[$name] = $matches[$index] ?? null;
                }

                $this->callAction($route['action'], $params);
                return;
            }
        }

        http_response_code(404);

        $controller = new \App\Controllers\MainController();
        $controller->renderError('404', 404);
    }

    /**
     * Appelle le controller et la méthode
     */
    private function callAction(string $action, array $params = []): void
    {
        if (!str_contains($action, '@'))
        {
            throw new \RuntimeException('Action invalide : ' . $action);
        }

        [$controllerName, $method] = explode('@', $action, 2);

        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass))
        {
            throw new \RuntimeException('Controller introuvable : ' . $controllerClass);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method))
        {
            throw new \RuntimeException('Méthode introuvable : ' . $controllerClass . '::' . $method);
        }

        call_user_func_array([$controller, $method], array_values($params));
    }
}