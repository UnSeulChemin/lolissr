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

            self::testSearchRoute(),

            self::testRoutePrefix(),

        ];
    }

    private static function testGetRoute(): array
    {
        $collection = new RouteCollection();

        $router = new Router(
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
        $collection = new RouteCollection();

        $router = new Router(
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

    private static function testSearchRoute(): array
    {
        $collection = new RouteCollection();

        $router = new Router(
            $collection,
        );

        $router->get(
            '/recherche/{slug}',
            static fn () => null,
        );

        $routes =
            $collection->all();

        return [
            'name' =>
                'Router clean search route',

            'success' =>
                count($routes) === 1,
        ];
    }

    private static function testRoutePrefix(): array
    {
        $collection = new RouteCollection();

        $router = new Router(
            $collection,
        );

        $router
            ->prefix('/api')
            ->get(
                '/search',
                static fn () => null,
            );

        return [
            'name' =>
                'Router prefix',

            'success' =>
                count(
                    $collection->all(),
                ) === 1,
        ];
    }
}