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

            self::testDuplicateRoute(),

            self::testList(),

        ];
    }

    private static function testAddRoute(): array
    {
        $collection =
            new RouteCollection();

        $route = new Route(
            'GET',
            '/manga',
            static fn () => null,
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

    private static function testDuplicateRoute(): array
    {
        $collection =
            new RouteCollection();

        $route = new Route(
            'GET',
            '/manga',
            static fn () => null,
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

    private static function testList(): array
    {
        $collection =
            new RouteCollection();

        $route = new Route(
            'GET',
            '/search',
            'SearchController@index',
        );

        $collection->add(
            $route,
        );

        $list =
            $collection->list();

        return [
            'name' =>
                'RouteCollection list',

            'success' =>
                isset($list[0])
                && str_contains(
                    $list[0],
                    '/search',
                )
                && str_contains(
                    $list[0],
                    'SearchController@index',
                ),
        ];
    }
}