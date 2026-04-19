<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Http\Router;

final class RoutesConfigTest extends TestCase
{
    public function testRoutesAreRegistered(): void
    {
        $router = new Router();

        $routesFile = ROOT . '/config/routes.php';

        $this->assertFileExists($routesFile);

        $routes = require $routesFile;

        $this->assertIsCallable($routes);

        $routes($router);

        $registeredRoutes = $router->getRoutes();

        $this->assertNotEmpty($registeredRoutes);
    }

    public function testMainRouteExists(): void
    {
        $router = new Router();

        $routes = require ROOT . '/config/routes.php';

        $routes($router);

        $routesList = $router->getRoutes();

        $this->assertArrayHasKey('GET', $routesList);

        $this->assertTrue(
            $this->routeExists(
                $routesList['GET'],
                '/'
            )
        );
    }

    public function testMangaShowRouteExists(): void
    {
        $router = new Router();

        $routes = require ROOT . '/config/routes.php';

        $routes($router);

        $routesList = $router->getRoutes();

        $this->assertTrue(
            $this->routeExists(
                $routesList['GET'],
                '/manga/{slug}/{numero}'
            )
        );
    }

    private function routeExists(array $routes, string $path): bool
    {
        foreach ($routes as $route)
        {
            if (($route['path'] ?? null) === $path)
            {
                return true;
            }
        }

        return false;
    }
}