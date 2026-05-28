<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Routing\Route;
use Framework\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RouteCollectionTest extends TestCase
{
    public function testAddRoute(): void
    {
        $collection =
            new RouteCollection();

        $route =
            new Route(
                'GET',
                '/manga',
                static fn () => null,
            );

        $collection->add(
            $route,
        );

        $this->assertCount(
            1,
            $collection->all(),
        );
    }

    public function testDuplicateRoute(): void
    {
        $collection =
            new RouteCollection();

        $route =
            new Route(
                'GET',
                '/manga',
                static fn () => null,
            );

        $collection->add(
            $route,
        );

        $this->expectException(
            RuntimeException::class,
        );

        $collection->add(
            $route,
        );
    }

    public function testList(): void
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

        $this->assertArrayHasKey(
            0,
            $list,
        );

        $this->assertStringContainsString(
            '/search',
            $list[0],
        );
    }
}