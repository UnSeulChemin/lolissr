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
        array $middlewares = []
    ): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri === '/'
                ? '/'
                : '/' . trim($uri, '/'),
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): void
    {
        $request = $this->resolve(Request::class);

        $method = $request->method();

        $uri = $request->path();

        foreach ($this->routes as $route)
        {
            $pattern = preg_replace(
                '#\{([^/]+)\}#',
                '([^/]+)',
                $route['uri']
            );

            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $uri, $matches))
            {
                continue;
            }

            if ($method !== $route['method'])
            {
                abort(405);
            }

            array_shift($matches);

            $this->runMiddlewares(
                $route['middlewares'],
                $request
            );

            $action = $route['action'];

            if (is_string($action))
            {
                [$controllerClass, $controllerMethod] = explode(
                    '@',
                    $action
                );

                $controllerClass =
                    'App\\Controllers\\'
                    . $controllerClass;
            }
            else
            {
                [$controllerClass, $controllerMethod] = $action;
            }

            $controller = $this->resolve(
                $controllerClass
            );

            $parameters = $this->resolveMethodDependencies(
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

        abort(404);
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
                    'Middleware introuvable : '
                    . $middlewareClass
                );
            }

            $middleware = $this->resolve(
                $middlewareClass
            );

            if (
                !$middleware instanceof MiddlewareInterface
            ) {
                throw new RuntimeException(
                    'Middleware invalide : '
                    . $middlewareClass
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
                    $dependencies[] = new $typeName(
                        $request
                    );

                    continue;
                }

                $dependencies[] = $this->resolve(
                    $typeName
                );

                continue;
            }

            if (isset($routeParameters[$routeIndex]))
            {
                $dependencies[] =
                    $routeParameters[$routeIndex];

                $routeIndex++;

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