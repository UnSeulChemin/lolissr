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
        $prefix = trim($prefix, '/');

        if ($prefix !== '')
        {
            $clone->groupPrefixes[] = $prefix;
        }

        return $clone;
    }

    /**
     * @param class-string|list<class-string> $middleware
     */
    public function middleware(array|string $middleware): self
    {
        $clone = clone $this;

        $clone->groupMiddlewares = [
            ...$clone->groupMiddlewares,
            ...(array) $middleware
        ];

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
            fn (): ?array => $this->matchRoute($method, $uri)
        );

        if ($match === null)
        {
            $this->throwRouteException($uri);
        }

        $route = $match['route'];
        $params = $match['params'];

        Profiler::measure(
            'middleware',
            function () use ($route, $request): void
            {
                $this->runMiddlewares($route, $request);
            }
        );

        Profiler::measure(
            'controller',
            function () use ($route, $params, $request): void
            {
                $this->executeAction($route, $params, $request);
            }
        );
    }

    // =========================================
    // ENREGISTREMENT
    // =========================================

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
            [
                ...$this->groupPrefixes,
                trim($path, '/')
            ],
            static fn (string $segment): bool => $segment !== ''
        );

        $this->collection->add(
            new Route(
                $method,
                '/' . implode('/', $segments),
                $action,
                [
                    ...$this->groupMiddlewares,
                    ...$middlewares
                ]
            )
        );
    }

    // =========================================
    // MATCHING
    // =========================================

    /**
     * @return array{
     *     route: Route,
     *     params: array<string, string|int>
     * }|null
     */
    private function matchRoute(string $method, string $uri): ?array
    {
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
                'params' => $route->castParameters($namedMatches)
            ];
        }

        return null;
    }

    private function throwRouteException(string $uri): never
    {
        $allowedMethods = $this->collection->allowedMethodsFor($uri);

        if ($allowedMethods !== [])
        {
            throw new MethodNotAllowedException(
                headers: [
                    'Allow' => implode(', ', $allowedMethods)
                ]
            );
        }

        throw new NotFoundException("Route non trouvée : {$uri}");
    }

    // =========================================
    // MIDDLEWARES
    // =========================================

    private function runMiddlewares(Route $route, Request $request): void
    {
        foreach ($route->getMiddlewares() as $middlewareClass)
        {
            $middleware = $this->container->get($middlewareClass);

            if (! $middleware instanceof MiddlewareInterface)
            {
                throw new RuntimeException("Invalid middleware: {$middlewareClass}");
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
    private function executeAction(Route $route, array $params, Request $request): void
    {
        $action = $route->getAction();

        if ($action instanceof Closure)
        {
            $action(...$params);

            return;
        }

        [$controllerClass, $methodName] = $this->resolveAction($action);

        $controller = Profiler::measure(
            'controller.resolve',
            fn (): object => $this->container->get($controllerClass)
        );

        $reflection = $this->reflection($controller, $methodName);

        $arguments = Profiler::measure(
            'controller.arguments',
            fn (): array => $this->resolveArguments(
                $controller,
                $reflection,
                $params,
                $request
            )
        );

        Profiler::measure(
            'controller.action',
            static function () use ($reflection, $controller, $arguments): void
            {
                $reflection->invokeArgs($controller, $arguments);
            }
        );
    }

    /**
     * @param array{class-string, string}|string $action
     *
     * @return array{class-string, string}
     */
    private function resolveAction(array|string $action): array
    {
        if (is_array($action))
        {
            return $action;
        }

        if (! str_contains($action, '@'))
        {
            throw new RuntimeException("Invalid route action: {$action}");
        }

        [$controllerClass, $methodName] = explode('@', $action, 2);

        if ($controllerClass === '' || $methodName === '')
        {
            throw new RuntimeException("Invalid route action: {$action}");
        }

        /** @var class-string $controllerClass */
        return [$controllerClass, $methodName];
    }

    private function reflection(object $controller, string $method): ReflectionMethod
    {
        $key = $controller::class . '::' . $method;

        return $this->methods[$key] ??= new ReflectionMethod($controller, $method);
    }

    /**
     * @param array<string, string|int> $params
     *
     * @return list<mixed>
     */
    private function resolveArguments(
        object $controller,
        ReflectionMethod $reflection,
        array $params,
        Request $request
    ): array {
        $arguments = [];

        foreach ($reflection->getParameters() as $parameter)
        {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin())
            {
                $className = $type->getName();

                $arguments[] = $className === Request::class
                    ? $request
                    : $this->container->get($className);

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
                    $reflection->getName()
                )
            );
        }

        return $arguments;
    }
}