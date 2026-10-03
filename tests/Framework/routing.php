<?php

declare(strict_types=1);
require dirname(__DIR__, 2) . '/tests/Support/bootstrap.php';

use Framework\Config\Config;
use Framework\Container\Container;
use Framework\Http\Request;
use Framework\Routing\Route;
use Framework\Routing\RouteCollection;
use Framework\Routing\Router;

$check = static function (bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
};
$collection = new RouteCollection();
$routes = [];
$add = static function (string $method, string $path) use ($collection, &$routes): void
{
    $route = new Route($method, $path, static function (): void
    {});
    $routes[] = $route;
    $collection->add($route);
};
$add('GET', '/unrelated');
$add('POST', '/items/new');
$add('GET', '/{section}/new');
$add('GET', '/items/{id:int}');
$add('GET', '/items/new');
$add('GET', '/prefix-{slug}/edit');
$add('POST', '/{section}/{id}');
$add('GET', '/');
// A method with no wildcard bucket must retain dynamic/static precedence too.
$add('PUT', '/items/{id:int}');
$add('PUT', '/items/new');
$add('PUT', '/items/{slug}');
for ($i = 0; $i < 200; $i++) $add('GET', '/section-' . $i . '/{id:int}');

$uris = ['/', '/items/new', '/items/42', '/section-199/12', '/prefix-demo/edit', '/missing', '/items/new/', '//items/new', '/items/new//'];
foreach ($uris as $uri)
{
    $expectedMethods = [];
    foreach ($routes as $route)
    {
        if (preg_match($route->pattern, $uri) === 1) $expectedMethods[] = $route->getMethod();
    }
    $check($collection->allowedMethodsFor($uri) === array_values(array_unique($expectedMethods)), 'Allow order changed: ' . $uri);
    foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method)
    {
        $first = static function (array $candidates) use ($method, $uri): ?Route
        {
            foreach ($candidates as $route)
            {
                if ($route->getMethod() === $method && preg_match($route->pattern, $uri) === 1) return $route;
            }
            return null;
        };
        $check($first($routes) === $first($collection->candidates($method, $uri)), 'Route precedence changed: ' . $uri);
    }
}

// Exercise actual dispatch: request injection, route parameters and scalar defaults.
$controller = new class
{
    public array $received = [];
    public function show(Request $request, int $id, string $label = 'default'): void
    {
        $this->received = [$request, $id, $label];
    }
};
$container = new Container();
$request = new Request(server: ['REQUEST_URI' => '/items/42', 'REQUEST_METHOD' => 'GET']);
$container->instance(Request::class, $request);
$container->instance($controller::class, $controller);
Config::prime(['app' => ['base_uri' => '/']]);
$router = new Router(new RouteCollection(), $container);
$router->get('/items/{id:int}', [$controller::class, 'show']);
$router->dispatch();
$check($controller->received === [$request, 42, 'default'], 'Controller arguments changed.');

class DefaultObjectFixture
{
    public function __construct(public mixed $value = new stdClass())
    {}
}
$first = $container->get(DefaultObjectFixture::class);
$second = $container->get(DefaultObjectFixture::class);
$check($first->value !== $second->value, 'Cached parameter plan reused an object default.');

interface UnboundFixture
{}
class NullableDependencyFixture
{
    public function __construct(public ?UnboundFixture $dependency = null)
    {}
}
$check($container->get(NullableDependencyFixture::class)->dependency === null, 'Nullable unbound dependency changed.');

if (in_array('--benchmark', $argv, true))
{
    $iterations = 2000;
    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++)
    {
        foreach ($routes as $route) preg_match($route->pattern, '/section-199/12');
    }
    $linear = (hrtime(true) - $start) / $iterations / 1e6;
    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) $collection->allowedMethodsFor('/section-199/12');
    $indexed = (hrtime(true) - $start) / $iterations / 1e6;
    printf("Allow lookup, %d synthetic routes: linear %.4f ms; indexed %.4f ms.\n", count($routes), $linear, $indexed);
}
echo "PASS: indexed routes preserve linear matching, Allow order and controller argument injection.\n";
