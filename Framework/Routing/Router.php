<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;
use Framework\Container\AppContainer;
use Framework\Http\Middleware\MiddlewareInterface;
use Framework\Http\Request;
use RuntimeException;

final class Router
{
    private RouteCollection $collection;

    private array $groupPrefixes = [];

    private array $groupMiddlewares = [];

    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    public function prefix(string $prefix): self
    {
        $clone = clone $this;

        $clone->groupPrefixes[] = trim($prefix, '/');

        return $clone;
    }

    public function middleware(array|string $middleware): self
    {
        $clone = clone $this;

        $clone->groupMiddlewares = array_merge(
            $clone->groupMiddlewares,
            (array) $middleware,
        );

        return $clone;
    }

    public function group(Closure $callback): void
    {
        $callback($this);
    }

    public function get(
        string $path,
        array|string|Closure $action,
        array $middlewares = [],
    ): void {
        $this->addRoute(
            'GET',
            $path,
            $action,
            $middlewares,
        );
    }

    public function post(
        string $path,
        array|string|Closure $action,
        array $middlewares = [],
    ): void {
        $this->addRoute(
            'POST',
            $path,
            $action,
            $middlewares,
        );
    }

    private function addRoute(
        string $method,
        string $path,
        array|string|Closure $action,
        array $middlewares,
    ): void {
        $fullPath = '/' . implode(
            '/',
            array_merge(
                $this->groupPrefixes,
                [trim($path, '/')],
            ),
        );

        $fullMiddlewares = array_merge(
            $this->groupMiddlewares,
            $middlewares,
        );

        $route = new Route(
            $method,
            $fullPath,
            $action,
            $fullMiddlewares,
        );

        $this->collection->add($route);
    }

    public function dispatch(): void
    {
        $request = AppContainer::get()->get(Request::class);

        $uri = $request->path();
        $method = $request->method();

        foreach ($this->collection->all() as $route) {
            if ($route->getMethod() !== $method) {
                continue;
            }

            if (!preg_match($route->pattern, $uri, $matches)) {
                continue;
            }

            array_shift($matches);

            foreach ($route->getMiddlewares() as $middlewareClass) {
                $middleware = AppContainer::get()->get($middlewareClass);

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new RuntimeException(
                        "Middleware invalide : {$middlewareClass}",
                    );
                }

                $middleware->handle($request);
            }

            $params = array_map(
                static function (string $value): string|int
                {
                    return ctype_digit($value)
                        ? (int) $value
                        : $value;
                },
                $matches,
            );

            $action = $route->getAction();

            if ($action instanceof Closure) {
                $action(...$params);

                return;
            }

            [$controllerClass, $methodName] = is_array($action)
                ? $action
                : explode('@', $action);

            $controller = AppContainer::get()->get($controllerClass);

            $controller->{$methodName}(...$params);

            return;
        }

        http_response_code(404);

        echo 'Route non trouvée : '
            . htmlspecialchars($uri);
    }
}