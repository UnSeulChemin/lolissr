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

            self::testMiddleware(),

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

        $routes =
            array_values(
                $collection->all(),
            );

        return [
            'name' =>
                'Router prefix',

            'success' =>
                isset($routes[0])
                && $routes[0]->getPath()
                    === '/api/search',
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

        $routes =
            array_values(
                $collection->all(),
            );

        return [
            'name' =>
                'Router middleware',

            'success' =>
                isset($routes[0])
                && $routes[0]->getMiddlewares()
                    === ['AuthMiddleware'],
        ];
    }
}