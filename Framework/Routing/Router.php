<?php

declare(strict_types=1);

namespace Framework\Routing;

use Framework\Container\Container;
use Framework\Debug\Profiler;
use Framework\Exceptions\MethodNotAllowedException;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Middleware\MiddlewareInterface;
use Framework\Http\Request;

use Closure;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Router
{
    /**
     * @var list<string>
     */
    private array $groupPrefixes = [];

    /**
     * @var list<class-string>
     */
    private array $groupMiddlewares = [];

    /**
     * @var array<string, ReflectionMethod>
     */
    private array $methods = [];

    public function __construct(
        private RouteCollection $collection,
        private Container $container
    ) {
    }

    // =========================================
    // GROUPES
    // =========================================

    public function prefix(string $prefix): self
    {
        $clone = clone $this;

        $clone->groupPrefixes[] = trim($prefix, '/');

        return $clone;
    }

    /**
     * @param class-string|list<class-string> $middleware
     */
    public function middleware(array|string $middleware): self
    {
        $clone = clone $this;

        $clone->groupMiddlewares = array_merge(
            $clone->groupMiddlewares,
            (array) $middleware
        );

        return $clone;
    }

    public function group(Closure $callback): void
    {
        $callback($this);
    }

    // =========================================
    // ROUTES
    // =========================================

    /**
     * @param array{class-string, string}|string|Closure $action
     * @param list<class-string> $middlewares
     */
    public function get(
        string $path,
        array|string|Closure $action,
        array $middlewares = []
    ): void {
        $this->addRoute('GET', $path, $action, $middlewares);
    }

    /**
     * @param array{class-string, string}|string|Closure $action
     * @param list<class-string> $middlewares
     */
    public function post(
        string $path,
        array|string|Closure $action,
        array $middlewares = []
    ): void {
        $this->addRoute('POST', $path, $action, $middlewares);
    }

    /**
     * @param array{class-string, string}|string|Closure $action
     * @param list<class-string> $middlewares
     */
    private function addRoute(
        string $method,
        string $path,
        array|string|Closure $action,
        array $middlewares
    ): void {
        $segments = array_filter(
            array_merge(
                $this->groupPrefixes,
                [trim($path, '/')]
            ),
            static fn (string $segment): bool => $segment !== ''
        );

        $fullPath = '/' . implode('/', $segments);

        $this->collection->add(
            new Route(
                $method,
                $fullPath,
                $action,
                array_merge(
                    $this->groupMiddlewares,
                    $middlewares
                )
            )
        );
    }

    // =========================================
    // DISPATCH
    // =========================================

    public function dispatch(): void
    {
        /** @var Request $request */
        $request = $this->container->get(Request::class);

        $uri = $request->path();
        $method = $request->method();

        $match = Profiler::measure(
            'route.match',
            fn (): ?array => $this->matchRoute(
                $method,
                $uri
            )
        );

        if ($match === null)
        {
            $allowedMethods = $this->collection->allowedMethodsFor($uri);

            if ($allowedMethods !== [])
            {
                throw new MethodNotAllowedException(
                    headers: [
                        'Allow' => implode(', ', $allowedMethods),
                    ]
                );
            }

            throw new NotFoundException(
                "Route non trouvée : {$uri}"
            );
        }

        [
            'route' => $route,
            'params' => $params,
        ] = $match;

        Profiler::measure(
            'middleware',
            fn (): null => $this->runMiddlewares(
                $route,
                $request
            )
        );

        Profiler::measure(
            'controller',
            fn (): null => $this->executeAction(
                $route,
                $params,
                $request
            )
        );
    }

    /**
     * @return array{
     *     route: Route,
     *     params: array<string, string|int>
     * }|null
     */
    private function matchRoute(
        string $method,
        string $uri
    ): ?array {
        foreach ($this->collection->forMethod($method) as $route)
        {
            if (preg_match($route->pattern, $uri, $matches) !== 1)
            {
                continue;
            }

            /** @var array<string, string> $namedMatches */
            $namedMatches = array_filter(
                $matches,
                static fn (int|string $key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            return [
                'route' => $route,
                'params' => $route->castParameters($namedMatches),
            ];
        }

        return null;
    }

    // =========================================
    // MIDDLEWARES
    // =========================================

    private function runMiddlewares(
        Route $route,
        Request $request
    ): void {
        foreach ($route->getMiddlewares() as $middlewareClass)
        {
            $middleware = $this->container->get($middlewareClass);

            if (! $middleware instanceof MiddlewareInterface)
            {
                throw new RuntimeException(
                    "Invalid middleware: {$middlewareClass}"
                );
            }

            $middleware->handle($request);
        }
    }

    // =========================================
    // ACTIONS
    // =========================================

    /**
     * @param array<string, string|int> $params
     */
    private function executeAction(
        Route $route,
        array $params,
        Request $request
    ): void {
        $action = $route->getAction();

        if ($action instanceof Closure)
        {
            $action(...$params);

            return;
        }

        if (! is_array($action))
        {
            if (! str_contains($action, '@'))
            {
                throw new RuntimeException(
                    "Invalid route action: {$action}"
                );
            }

            [$controllerClass, $methodName] = explode(
                '@',
                $action,
                2
            );
        }
        else
        {
            [$controllerClass, $methodName] = $action;
        }

        $controller = Profiler::measure(
            'controller.resolve',
            fn (): object => $this->container->get($controllerClass)
        );

        $arguments = Profiler::measure(
            'controller.arguments',
            fn (): array => $this->resolveArguments(
                $controller,
                $methodName,
                $params,
                $request
            )
        );

        $key = $controller::class . '::' . $methodName;

        $reflection = $this->methods[$key]
            ??= new ReflectionMethod($controller, $methodName);

        Profiler::measure(
            'controller.action',
            static function () use ($reflection, $controller, $arguments): void {
                $reflection->invokeArgs($controller, $arguments);
            }
        );
    }

    /**
     * @param array<string, string|int> $params
     *
     * @return list<mixed>
     */
    private function resolveArguments(
        object $controller,
        string $method,
        array $params,
        Request $request
    ): array {
        $key = $controller::class . '::' . $method;

        $reflection = $this->methods[$key]
            ??= new ReflectionMethod(
                $controller,
                $method
            );

        $arguments = [];

        foreach ($reflection->getParameters() as $parameter)
        {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin())
            {
                $className = $type->getName();

                if ($className === Request::class)
                {
                    $arguments[] = $request;

                    continue;
                }

                $arguments[] = $this->container->get(
                    $className
                );

                continue;
            }

            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $params))
            {
                $arguments[] = $params[$parameterName];

                continue;
            }

            if ($parameter->isDefaultValueAvailable())
            {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            throw new RuntimeException(
                sprintf(
                    'Unable to resolve parameter "%s" in %s::%s()',
                    $parameterName,
                    $controller::class,
                    $method
                )
            );
        }

        return $arguments;
    }
}