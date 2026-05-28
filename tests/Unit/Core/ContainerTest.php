<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Container\Container;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContainerTest extends TestCase
{
    public function testBind(): void
    {
        $container =
            new Container();

        $container->bind(
            TestService::class,
        );

        $service =
            $container->get(
                TestService::class,
            );

        $this->assertInstanceOf(
            TestService::class,
            $service,
        );
    }

    public function testSingleton(): void
    {
        $container =
            new Container();

        $container->singleton(
            TestService::class,
        );

        $first =
            $container->get(
                TestService::class,
            );

        $second =
            $container->get(
                TestService::class,
            );

        $this->assertSame(
            $first,
            $second,
        );
    }

    public function testAutowiring(): void
    {
        $container =
            new Container();

        $service =
            $container->get(
                TestDependencyService::class,
            );

        $this->assertInstanceOf(
            TestDependencyService::class,
            $service,
        );
    }

    public function testMissingClass(): void
    {
        $container =
            new Container();

        $this->expectException(
            RuntimeException::class,
        );

        $container->get(
            'MissingClass',
        );
    }

    public function testCircularDependencyPlaceholder(): void
    {
        $this->assertTrue(
            true,
        );
    }
}

final class TestService
{
}

final class TestDependencyService
{
    public function __construct(
        private readonly TestService $service,
    ) {
    }
}