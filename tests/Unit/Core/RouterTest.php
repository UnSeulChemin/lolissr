<?php

declare(strict_types=1);

namespace App\Controllers
{
    final class TestRouterController
    {
        public static array $called = [];

        public static function reset(): void
        {
            self::$called = [];
        }

        public function index(): void
        {
            self::$called = [
                'method' => 'index',
                'params' => [],
            ];
        }

        public function show(string $slug, string $numero): void
        {
            self::$called = [
                'method' => 'show',
                'params' => [$slug, $numero],
            ];
        }

        public function store(): void
        {
            self::$called = [
                'method' => 'store',
                'params' => [],
            ];
        }
    }
}

namespace
{
    use App\Controllers\TestRouterController;
    use App\Core\Functions;
    use App\Core\Router;
    use PHPUnit\Framework\TestCase;

    final class RouterTest extends TestCase
    {
        protected function setUp(): void
        {
            TestRouterController::reset();
        }

        protected function tearDown(): void
        {
            TestRouterController::reset();
        }

        public function testGetRegistersAndDispatchesSimpleRoute(): void
        {
            $router = new Router();

            $router->get('/manga', 'TestRouterController@index');
            $router->dispatch('/manga', 'GET');

            $this->assertSame([
                'method' => 'index',
                'params' => [],
            ], TestRouterController::$called);
        }

        public function testPostRegistersAndDispatchesSimpleRoute(): void
        {
            $router = new Router();

            $router->post('/manga', 'TestRouterController@store');
            $router->dispatch('/manga', 'POST');

            $this->assertSame([
                'method' => 'store',
                'params' => [],
            ], TestRouterController::$called);
        }

        public function testDispatchAcceptsLowercaseHttpMethod(): void
        {
            $router = new Router();

            $router->get('/manga', 'TestRouterController@index');
            $router->dispatch('/manga', 'get');

            $this->assertSame([
                'method' => 'index',
                'params' => [],
            ], TestRouterController::$called);
        }

        public function testDispatchMatchesDynamicRouteAndPassesParameters(): void
        {
            $router = new Router();

            $router->get('/manga/{slug}/{numero}', 'TestRouterController@show');
            $router->dispatch('/manga/one-piece/1', 'GET');

            $this->assertSame([
                'method' => 'show',
                'params' => ['one-piece', '1'],
            ], TestRouterController::$called);
        }

        public function testDispatchAcceptsTrailingSlash(): void
        {
            $router = new Router();

            $router->get('/manga/{slug}/{numero}', 'TestRouterController@show');
            $router->dispatch('/manga/one-piece/1/', 'GET');

            $this->assertSame([
                'method' => 'show',
                'params' => ['one-piece', '1'],
            ], TestRouterController::$called);
        }

        public function testDispatchIgnoresQueryString(): void
        {
            $router = new Router();

            $router->get('/manga/{slug}/{numero}', 'TestRouterController@show');
            $router->dispatch('/manga/one-piece/1?tri=desc&page=2', 'GET');

            $this->assertSame([
                'method' => 'show',
                'params' => ['one-piece', '1'],
            ], TestRouterController::$called);
        }

        public function testDispatchMatchesRootRoute(): void
        {
            $router = new Router();

            $router->get('/', 'TestRouterController@index');
            $router->dispatch('/', 'GET');

            $this->assertSame([
                'method' => 'index',
                'params' => [],
            ], TestRouterController::$called);
        }

        public function testDispatchStripsBasePathBeforeMatching(): void
        {
            $basePath = Functions::basePath();

            if ($basePath === '/')
            {
                $this->markTestSkipped('Aucun base path configuré à tester.');
            }

            $router = new Router();

            $router->get('/manga/{slug}/{numero}', 'TestRouterController@show');
            $router->dispatch(rtrim($basePath, '/') . '/manga/one-piece/1', 'GET');

            $this->assertSame([
                'method' => 'show',
                'params' => ['one-piece', '1'],
            ], TestRouterController::$called);
        }

        public function testDispatchThrowsExceptionWhenActionFormatIsInvalid(): void
        {
            $router = new Router();

            $router->get('/manga', 'ActionInvalide');

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Action invalide');

            $router->dispatch('/manga', 'GET');
        }

        public function testDispatchThrowsExceptionWhenControllerDoesNotExist(): void
        {
            $router = new Router();

            $router->get('/manga', 'FakeController@index');

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Controller introuvable');

            $router->dispatch('/manga', 'GET');
        }

        public function testDispatchThrowsExceptionWhenMethodDoesNotExist(): void
        {
            $router = new Router();

            $router->get('/manga', 'TestRouterController@fakeMethod');

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Méthode introuvable');

            $router->dispatch('/manga', 'GET');
        }

        public function testFindAllowedMethodsReturnsUniqueMatchingMethods(): void
        {
            $router = new Router();

            $router->get('/manga/{slug}', 'TestRouterController@index');
            $router->post('/manga/{slug}', 'TestRouterController@store');
            $router->get('/autre-route', 'TestRouterController@index');

            $reflection = new \ReflectionClass($router);
            $method = $reflection->getMethod('findAllowedMethods');

            $result = $method->invoke($router, '/manga/one-piece');

            $this->assertSame(['GET', 'POST'], $result);
        }

        public function testFindAllowedMethodsReturnsEmptyArrayWhenNoRouteMatches(): void
        {
            $router = new Router();

            $router->get('/manga/{slug}', 'TestRouterController@index');

            $reflection = new \ReflectionClass($router);
            $method = $reflection->getMethod('findAllowedMethods');

            $result = $method->invoke($router, '/inconnue');

            $this->assertSame([], $result);
        }

        public function testAddRouteBuildsExpectedPatternAndParams(): void
        {
            $router = new Router();

            $router->get('/manga/{slug}/{numero}', 'TestRouterController@show');

            $reflection = new \ReflectionClass($router);
            $property = $reflection->getProperty('routes');
            $routes = $property->getValue($router);

            $this->assertArrayHasKey('GET', $routes);
            $this->assertCount(1, $routes['GET']);

            $route = $routes['GET'][0];

            $this->assertSame('/manga/{slug}/{numero}', $route['path']);
            $this->assertSame('TestRouterController@show', $route['action']);
            $this->assertSame('#^/manga/([^/]+)/([^/]+)/?$#', $route['pattern']);
            $this->assertSame(['slug', 'numero'], $route['params']);
        }

        public function testAddRouteBuildsExpectedPatternForRootPath(): void
        {
            $router = new Router();

            $router->get('/', 'TestRouterController@index');

            $reflection = new \ReflectionClass($router);
            $property = $reflection->getProperty('routes');
            $routes = $property->getValue($router);

            $this->assertSame('#^/$#', $routes['GET'][0]['pattern']);
        }
    }
}