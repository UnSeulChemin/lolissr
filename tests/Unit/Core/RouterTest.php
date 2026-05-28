<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Routing\RouteCollection;
use Framework\Routing\Router;

final class RouterTest
{
    public static function run(): array
    {
        return [

            self::testGetRoute(),

            self::testPostRoute(),

            self::testPrefix(),

            self::testNestedPrefix(),

            self::testMiddleware(),

            self::testMultipleMiddlewares(),

            self::testGroupRoutes(),

            self::testRoutePath(),

            self::testRouteMethod(),

        ];
    }

    private static function testGetRoute(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router->get(
            '/manga',
            static fn () => null,
        );

        return [
            'name' =>
                'Router GET route',

            'success' =>
                count(
                    $collection->all(),
                ) === 1,
        ];
    }

    private static function testPostRoute(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router->post(
            '/update',
            static fn () => null,
        );

        return [
            'name' =>
                'Router POST route',

            'success' =>
                count(
                    $collection->all(),
                ) === 1,
        ];
    }

    private static function testPrefix(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router
            ->prefix('/api')
            ->get(
                '/search',
                static fn () => null,
            );

        $route =
            array_values(
                $collection->all(),
            )[0];

        return [
            'name' =>
                'Router prefix',

            'success' =>
                $route->getPath()
                === '/api/search',
        ];
    }

    private static function testNestedPrefix(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router
            ->prefix('/api')
            ->prefix('/v1')
            ->get(
                '/search',
                static fn () => null,
            );

        $route =
            array_values(
                $collection->all(),
            )[0];

        return [
            'name' =>
                'Router nested prefix',

            'success' =>
                $route->getPath()
                === '/api/v1/search',
        ];
    }

    private static function testMiddleware(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router
            ->middleware(
                'AuthMiddleware',
            )
            ->get(
                '/admin',
                static fn () => null,
            );

        $route =
            array_values(
                $collection->all(),
            )[0];

        return [
            'name' =>
                'Router middleware',

            'success' =>
                $route->getMiddlewares()
                === ['AuthMiddleware'],
        ];
    }

    private static function testMultipleMiddlewares(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router
            ->middleware([
                'AuthMiddleware',
                'AdminMiddleware',
            ])
            ->get(
                '/admin',
                static fn () => null,
            );

        $route =
            array_values(
                $collection->all(),
            )[0];

        return [
            'name' =>
                'Router multiple middlewares',

            'success' =>
                $route->getMiddlewares()
                === [
                    'AuthMiddleware',
                    'AdminMiddleware',
                ],
        ];
    }

    private static function testGroupRoutes(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router->group(
            static function (
                Router $router,
            ): void {

                $router->get(
                    '/one',
                    static fn () => null,
                );

                $router->get(
                    '/two',
                    static fn () => null,
                );
            },
        );

        return [
            'name' =>
                'Router group',

            'success' =>
                count(
                    $collection->all(),
                ) === 2,
        ];
    }

    private static function testRoutePath(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router->get(
            '/manga/{slug}',
            static fn () => null,
        );

        $route =
            array_values(
                $collection->all(),
            )[0];

        return [
            'name' =>
                'Router route path',

            'success' =>
                $route->getPath()
                === '/manga/{slug}',
        ];
    }

    private static function testRouteMethod(): array
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router->post(
            '/save',
            static fn () => null,
        );

        $route =
            array_values(
                $collection->all(),
            )[0];

        return [
            'name' =>
                'Router route method',

            'success' =>
                $route->getMethod()
                === 'POST',
        ];
    }
}