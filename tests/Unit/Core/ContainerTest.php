<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Container\Container;
use RuntimeException;

final class ContainerTest
{
    public static function run(): array
    {
        return [

            self::testBind(),

            self::testSingleton(),

            self::testAutowiring(),

            self::testFactoryBinding(),

            self::testFactoryMustReturnObject(),

            self::testDefaultValueResolution(),

            self::testMissingClass(),

            self::testNonInstantiableClass(),

            self::testCircularDependency(),

        ];
    }

    private static function testBind(): array
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

        return [
            'name' =>
                'Container bind',

            'success' =>
                $service instanceof TestService,
        ];
    }

    private static function testSingleton(): array
    {
        $container =
            new Container();

        $container->singleton(
            TestService::class,
        );

        $a =
            $container->get(
                TestService::class,
            );

        $b =
            $container->get(
                TestService::class,
            );

        return [
            'name' =>
                'Container singleton',

            'success' =>
                $a === $b,
        ];
    }

    private static function testAutowiring(): array
    {
        $container =
            new Container();

        $service =
            $container->get(
                TestDependencyService::class,
            );

        return [
            'name' =>
                'Container autowiring',

            'success' =>
                $service instanceof TestDependencyService,
        ];
    }

    private static function testFactoryBinding(): array
    {
        $container =
            new Container();

        $container->bind(
            TestService::class,
            static fn (): TestService => new TestService(),
        );

        $service =
            $container->get(
                TestService::class,
            );

        return [
            'name' =>
                'Container factory binding',

            'success' =>
                $service instanceof TestService,
        ];
    }

    private static function testFactoryMustReturnObject(): array
    {
        $container =
            new Container();

        $container->bind(
            TestService::class,
            static fn (): string => 'invalid',
        );

        $success = false;

        try {

            $container->get(
                TestService::class,
            );

        } catch (RuntimeException) {

            $success = true;
        }

        return [
            'name' =>
                'Container factory object validation',

            'success' =>
                $success,
        ];
    }

    private static function testDefaultValueResolution(): array
    {
        $container =
            new Container();

        $service =
            $container->get(
                DefaultValueService::class,
            );

        return [
            'name' =>
                'Container default value',

            'success' =>
                $service instanceof DefaultValueService,
        ];
    }

    private static function testMissingClass(): array
    {
        $container =
            new Container();

        $success = false;

        try {

            $container->get(
                'MissingClass',
            );

        } catch (RuntimeException) {

            $success = true;
        }

        return [
            'name' =>
                'Container missing class',

            'success' =>
                $success,
        ];
    }

    private static function testNonInstantiableClass(): array
    {
        $container =
            new Container();

        $success = false;

        try {

            $container->get(
                TestInterface::class,
            );

        } catch (RuntimeException) {

            $success = true;
        }

        return [
            'name' =>
                'Container non instantiable',

            'success' =>
                $success,
        ];
    }

    private static function testCircularDependency(): array
    {
        $container =
            new Container();

        $success = false;

        try {

            $container->get(
                CircularA::class,
            );

        } catch (RuntimeException) {

            $success = true;
        }

        return [
            'name' =>
                'Container circular dependency',

            'success' =>
                $success,
        ];
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

final class DefaultValueService
{
    public function __construct(
        private string $name = 'test',
    ) {
    }
}

interface TestInterface
{
}

final class CircularA
{
    public function __construct(
        CircularB $service,
    ) {
    }
}

final class CircularB
{
    public function __construct(
        CircularA $service,
    ) {
    }
}