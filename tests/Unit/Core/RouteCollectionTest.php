<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Routing\Route;
use Framework\Routing\RouteCollection;
use RuntimeException;

final class RouteCollectionTest
{
    public static function run(): array
    {
        return [

            self::testAddRoute(),

            self::testAll(),

            self::testDuplicateRoute(),

            self::testListStringAction(),

            self::testListArrayAction(),

            self::testEmptyList(),

        ];
    }

    private static function testAddRoute(): array
    {
        $collection =
            new RouteCollection();

        $route = new Route(
            'GET',
            '/manga',
            static fn (): null => null,
        );

        $collection->add(
            $route,
        );

        return [
            'name' =>
                'RouteCollection add route',

            'success' =>
                count(
                    $collection->all(),
                ) === 1,
        ];
    }

    private static function testAll(): array
    {
        $collection =
            new RouteCollection();

        $route = new Route(
            'GET',
            '/manga',
            static fn (): null => null,
        );

        $collection->add(
            $route,
        );

        $routes =
            $collection->all();

        return [
            'name' =>
                'RouteCollection all',

            'success' =>
                count($routes) === 1,
        ];
    }

    private static function testDuplicateRoute(): array
    {
        $collection =
            new RouteCollection();

        $route = new Route(
            'GET',
            '/manga',
            static fn (): null => null,
        );

        $collection->add(
            $route,
        );

        $success = false;

        try {

            $collection->add(
                $route,
            );

        } catch (RuntimeException) {

            $success = true;
        }

        return [
            'name' =>
                'RouteCollection duplicate route',

            'success' =>
                $success,
        ];
    }

    private static function testListStringAction(): array
    {
        $collection =
            new RouteCollection();

        $collection->add(
            new Route(
                'GET',
                '/search',
                'SearchController@index',
            ),
        );

        $list =
            $collection->list();

        return [
            'name' =>
                'RouteCollection string action',

            'success' =>
                isset($list[0])
                && str_contains(
                    $list[0],
                    'SearchController@index',
                ),
        ];
    }

    private static function testListArrayAction(): array
    {
        $collection =
            new RouteCollection();

        $collection->add(
            new Route(
                'GET',
                '/search',
                [
                    TestController::class,
                    'index',
                ],
            ),
        );

        $list =
            $collection->list();

        return [
            'name' =>
                'RouteCollection array action',

            'success' =>
                isset($list[0])
                && str_contains(
                    $list[0],
                    '[Controller::class, method]',
                ),
        ];
    }

    private static function testEmptyList(): array
    {
        $collection =
            new RouteCollection();

        return [
            'name' =>
                'RouteCollection empty list',

            'success' =>
                $collection->list() === [],
        ];
    }
}

final class TestController
{
}