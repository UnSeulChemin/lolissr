<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Container\AppContainer;
use App\Core\Http\Middleware\MiddlewareInterface;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Router
{
    private array $routes = [];

    public function get(
        string $uri,
        array|string $action,
        array $middlewares = []
    ): void {
        $this->addRoute(
            'GET',
            $uri,
            $action,
            $middlewares
        );
    }

    public function post(
        string $uri,
        array|string $action,
        array $middlewares = []
    ): void {
        $this->addRoute(
            'POST',
            $uri,
            $action,
            $middlewares
        );
    }

    private function addRoute(
        string $method,
        string $uri,
        array|string $action,
        array $middlewares
    ): void {
        $uri = '/' . trim($uri, '/');

        if ($uri === '//')
        {
            $uri = '/';
        }

        $pattern = preg_replace(
            '#\{[^/]+\}#',
            '([^/]+)',
            $uri
        );

        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'pattern' => '#^' . $pattern . '$#',
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): void
    {
        $request = $this->resolve(Request::class);

        $method = $request->method();
        $uri = $request->path();

        $methodNotAllowed = false;

        foreach ($this->routes as $route)
        {
            if (
                !preg_match(
                    $route['pattern'],
                    $uri,
                    $matches
                )
            ) {
                continue;
            }

            if ($method !== $route['method'])
            {
                $methodNotAllowed = true;

                continue;
            }

            array_shift($matches);

            $this->runMiddlewares(
                $route['middlewares'],
                $request
            );

            [$controller, $controllerMethod] =
                $this->resolveAction(
                    $route['action']
                );

            $parameters =
                $this->resolveMethodDependencies(
                    $controller,
                    $controllerMethod,
                    $matches,
                    $request
                );

            $controller->{$controllerMethod}(
                ...$parameters
            );

            return;
        }

        abort($methodNotAllowed ? 405 : 404);
    }

    private function resolveAction(
        array|string $action
    ): array {
        if (is_string($action))
        {
            [$controller, $method] =
                explode('@', $action);

            $controller =
                'App\\Controllers\\'
                . $controller;
        }
        else
        {
            [$controller, $method] = $action;
        }

        return [
            $this->resolve($controller),
            $method
        ];
    }

    private function runMiddlewares(
        array $middlewares,
        Request $request
    ): void {
        foreach ($middlewares as $middlewareClass)
        {
            if (!class_exists($middlewareClass))
            {
                throw new RuntimeException(
                    "Middleware introuvable : {$middlewareClass}"
                );
            }

            $middleware = $this->resolve(
                $middlewareClass
            );

            if (
                !$middleware instanceof MiddlewareInterface
            ) {
                throw new RuntimeException(
                    "Middleware invalide : {$middlewareClass}"
                );
            }

            $middleware->handle($request);
        }
    }

    private function resolveMethodDependencies(
        object $controller,
        string $method,
        array $routeParameters,
        Request $request
    ): array {
        $reflection = new ReflectionMethod(
            $controller,
            $method
        );

        $dependencies = [];

        $routeIndex = 0;

        foreach (
            $reflection->getParameters()
            as $parameter
        ) {
            $type = $parameter->getType();

            if (
                $type instanceof ReflectionNamedType
                && !$type->isBuiltin()
            ) {
                $typeName = $type->getName();

                if ($typeName === Request::class)
                {
                    $dependencies[] = $request;

                    continue;
                }

                if (
                    is_subclass_of(
                        $typeName,
                        FormRequest::class
                    )
                ) {
                    $dependencies[] =
                        new $typeName($request);

                    continue;
                }

                $dependencies[] =
                    $this->resolve($typeName);

                continue;
            }

            if (isset($routeParameters[$routeIndex]))
            {
                $dependencies[] =
                    $routeParameters[$routeIndex++];

                continue;
            }

            if ($parameter->isDefaultValueAvailable())
            {
                $dependencies[] =
                    $parameter->getDefaultValue();

                continue;
            }

            throw new RuntimeException(
                'Impossible de résoudre le paramètre : '
                . $parameter->getName()
            );
        }

        return $dependencies;
    }

    private function resolve(
        string $class
    ): object {
        return AppContainer::get()->get($class);
    }
}