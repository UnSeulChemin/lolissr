<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;
use Framework\Container\Container;
use Framework\Container\AppContainer;
use Framework\Exceptions\NotFoundException;
use Framework\Http\Middleware\MiddlewareInterface;
use Framework\Http\Request;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Router
{
    private RouteCollection $collection;

    /**
     * @var list<string>
     */
    private array $groupPrefixes = [];

    /**
     * @var list<class-string>
     */
    private array $groupMiddlewares = [];

    public function __construct(
        RouteCollection $collection,
    ) {
        $this->collection = $collection;
    }

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

        $clone->groupMiddlewares = array_merge($clone->groupMiddlewares, (array) $middleware);

        return $clone;
    }

    public function group(Closure $callback): void
    {
        $callback($this);
    }

    /**
     * @param array{class-string,string}|string|Closure $action
     * @param list<class-string> $middlewares
     */
    public function get(string $path, array|string|Closure $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $action, $middlewares);
    }

    /**
     * @param array{class-string,string}|string|Closure $action
     * @param list<class-string> $middlewares
     */
    public function post(string $path, array|string|Closure $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $action, $middlewares);
    }

    /**
     * @param array{class-string,string}|string|Closure $action
     * @param list<class-string> $middlewares
     */
    private function addRoute(string $method, string $path, array|string|Closure $action, array $middlewares): void
    {
        $segments =
            array_filter(
                array_merge($this->groupPrefixes, [trim($path, '/')]),
                static fn (string $segment): bool
                    => $segment !== ''
            );

        $fullPath = '/' . implode('/', $segments);

        $this->collection->add(new Route($method, $fullPath, $action, array_merge($this->groupMiddlewares, $middlewares)));
    }

    /**
     * Match and execute current request.
     */
    public function dispatch(): void
    {
        $container = AppContainer::get();

        /** @var Request $request */
        $request = $container->get(Request::class);

        $uri = $request->path();

        $method = $request->method();

        foreach ($this->collection->forMethod($method) as $route)
        {

            if (preg_match($route->pattern, $uri, $matches) !== 1)
            {
                continue;
            }

            array_shift($matches);

            $this->runMiddlewares($container, $route, $request);

            /** @var list<int|string> $params */
            $params = array_values(
                array_map(
                    static function (string $value): string|int
                    {
                        $value = rawurldecode($value);

                        return ctype_digit($value) ? (int) $value : $value;
                    },
                    $matches,
                ),
            );

            $this->executeAction($container, $route, $params, $request);

            return;
        }

        throw new NotFoundException("Route non trouvée : {$uri}");
    }

    /**
     * Execute all route middlewares.
     */
    private function runMiddlewares(Container $container, Route $route, Request $request): void
    {
        foreach ($route->getMiddlewares() as $middlewareClass)
        {
            $middleware = $container->get($middlewareClass);

            if (! $middleware instanceof MiddlewareInterface)
            {
                throw new RuntimeException("Invalid middleware: {$middlewareClass}");
            }

            $middleware->handle($request);
        }
    }

    /**
     * Execute route action.
     *
     * @param list<string|int> $params
     */
    private function executeAction(Container $container, Route $route, array $params, Request $request): void
    {
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
                throw new RuntimeException("Invalid route action: {$action}");
            }

            $action = explode('@', $action, 2);
        }

        [$controllerClass, $methodName] = $action;

        $controller = $container->get($controllerClass);

        if (! method_exists($controller, $methodName))
        {
            throw new RuntimeException(sprintf('Method %s::%s does not exist.', $controller::class, $methodName));
        }

        $arguments = $this->resolveArguments($container, $controller, $methodName, $params, $request);

        /** @phpstan-ignore-next-line */
        $controller->{$methodName}(...$arguments);
    }

    /**
     * Resolve controller arguments.
     *
     * @param list<string|int> $params
     * @return list<mixed>
     */
    private function resolveArguments(
        Container $container,
        object $controller,
        string $method,
        array $params,
        Request $request,
    ): array {

        $reflection = new ReflectionMethod($controller, $method);

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

                $arguments[] = $container->get($className);

                continue;
            }

            if ($params !== [])
            {
                $arguments[] = array_shift($params);

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
                    $parameter->getName(),
                    $controller::class,
                    $method,
                ),
            );
        }

        return $arguments;
    }
}
