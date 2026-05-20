<?php

declare(strict_types=1);

namespace Framework\Http;

use App\Core\Container\AppContainer;
use Framework\Http\Middleware\MiddlewareInterface;
use Closure;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Router
{
    /**
     * @var list<array{
     *     method: string,
     *     uri: string,
     *     pattern: string,
     *     action: array<int, string>|string|Closure,
     *     middlewares: list<class-string>
     * }>
     */
    private array $routes = [];

    /**
     * @param array<int, string>|string|Closure $action
     * @param list<class-string> $middlewares
     */
    public function get(
        string $uri,
        array|string|Closure $action,
        array $middlewares = [],
    ): void {
        $this->addRoute(
            'GET',
            $uri,
            $action,
            $middlewares,
        );
    }

    /**
     * @param array<int, string>|string|Closure $action
     * @param list<class-string> $middlewares
     */
    public function post(
        string $uri,
        array|string|Closure $action,
        array $middlewares = [],
    ): void {
        $this->addRoute(
            'POST',
            $uri,
            $action,
            $middlewares,
        );
    }

    /**
     * @param array<int, string>|string|Closure $action
     * @param list<class-string> $middlewares
     */
    private function addRoute(
        string $method,
        string $uri,
        array|string|Closure $action,
        array $middlewares,
    ): void {
        $uri = '/' . trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        $pattern = preg_replace_callback(
            '#\{([a-zA-Z0-9_]+)(?::([a-zA-Z]+))?\}#',
            static function (array $matches): string {
                $type = $matches[2] ?? 'string';

                return match ($type) {
                    'int' => '([0-9]+)',
                    default => '([^/]+)',
                };
            },
            $uri,
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
        /** @var Request $request */
        $request = $this->resolve(
            Request::class,
        );

        $method = $request->method();

        $uri = $request->path();

        $methodNotAllowed = false;

        foreach ($this->routes as $route) {
            if (
                !preg_match(
                    $route['pattern'],
                    $uri,
                    $matches,
                )
            ) {
                continue;
            }

            if ($method !== $route['method']) {
                $methodNotAllowed = true;

                continue;
            }

            array_shift($matches);

            $this->runMiddlewares(
                $route['middlewares'],
                $request,
            );

            if ($route['action'] instanceof Closure) {
                ($route['action'])(...$matches);

                return;
            }

            [
                $controller,
                $controllerMethod,
            ] = $this->resolveAction(
                $route['action'],
            );

            $parameters =
                $this->resolveMethodDependencies(
                    $controller,
                    $controllerMethod,
                    $matches,
                    $request,
                );

            /** @var callable $callable */
            $callable = [
                $controller,
                $controllerMethod,
            ];

            $callable(...$parameters);

            return;
        }

        abort(
            $methodNotAllowed
                ? 405
                : 404,
        );
    }

    /**
     * @param array<int, string>|string $action
     * @return array{
     *     0: object,
     *     1: string
     * }
     */
    private function resolveAction(
        array|string $action,
    ): array {
        if (is_string($action)) {
            [
                $controller,
                $method,
            ] = explode('@', $action);

            $controller =
                'App\\Controllers\\'
                . $controller;
        } else {
            [
                $controller,
                $method,
            ] = $action;
        }

        return [
            $this->resolve($controller),
            $method,
        ];
    }

    /**
     * @param list<class-string> $middlewares
     */
    private function runMiddlewares(
        array $middlewares,
        Request $request,
    ): void {
        foreach ($middlewares as $middlewareClass) {
            if (!class_exists($middlewareClass)) {
                throw new RuntimeException(
                    "Middleware introuvable : {$middlewareClass}",
                );
            }

            $middleware = $this->resolve(
                $middlewareClass,
            );

            if (
                !$middleware instanceof MiddlewareInterface
            ) {
                throw new RuntimeException(
                    "Middleware invalide : {$middlewareClass}",
                );
            }

            $middleware->handle($request);
        }
    }

    /**
     * @param list<string> $routeParameters
     * @return list<mixed>
     */
    private function resolveMethodDependencies(
        object $controller,
        string $method,
        array $routeParameters,
        Request $request,
    ): array {
        $reflection = new ReflectionMethod(
            $controller,
            $method,
        );

        $dependencies = [];

        $routeIndex = 0;

        foreach (
            $reflection->getParameters() as $parameter
        ) {
            $type = $parameter->getType();

            if (
                $type instanceof ReflectionNamedType
                && !$type->isBuiltin()
            ) {
                $typeName = $type->getName();

                if ($typeName === Request::class) {
                    $dependencies[] = $request;

                    continue;
                }

                if (
                    is_subclass_of(
                        $typeName,
                        FormRequest::class,
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

            if (isset($routeParameters[$routeIndex])) {
                $value =
                    $routeParameters[$routeIndex++];

                if (
                    $type instanceof ReflectionNamedType
                ) {
                    $value = $this->castRouteParameter(
                        $value,
                        $type->getName(),
                    );
                }

                if ($value === null) {
                    abort(404);
                }

                $dependencies[] = $value;

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] =
                    $parameter->getDefaultValue();

                continue;
            }

            throw new RuntimeException(
                'Impossible de résoudre le paramètre : '
                . $parameter->getName(),
            );
        }

        return $dependencies;
    }

    private function castRouteParameter(
        string $value,
        string $type,
    ): mixed {
        return match ($type) {
            'int' => ctype_digit($value)
                ? (int) $value
                : null,

            'float' => is_numeric($value)
                ? (float) $value
                : null,

            'bool' => filter_var(
                $value,
                FILTER_VALIDATE_BOOL,
                FILTER_NULL_ON_FAILURE,
            ),

            'string' => $value,

            default => $value,
        };
    }

    private function resolve(
        string $class,
    ): object {
        return AppContainer::get()
            ->get($class);
    }
}
