<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Routing\RouteCollection;
use Framework\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testGetRoute(): void
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

        $this->assertCount(
            1,
            $collection->all(),
        );
    }

    public function testPostRoute(): void
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

        $this->assertCount(
            1,
            $collection->all(),
        );
    }

    public function testSearchRoute(): void
    {
        $collection =
            new RouteCollection();

        $router =
            new Router(
                $collection,
            );

        $router->get(
            '/recherche/{slug}',
            static fn () => null,
        );

        $this->assertCount(
            1,
            $collection->all(),
        );
    }

    public function testRoutePrefix(): void
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

        $this->assertCount(
            1,
            $collection->all(),
        );
    }
}