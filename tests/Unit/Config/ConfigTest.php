<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\Config;
use ReflectionClass;

final class ConfigTest
{
    public static function run(): array
    {
        return [

            self::testSegments(),

            self::testArrayGet(),

            self::testArrayGetDefault(),

            self::testArrayHas(),

            self::testArrayHasMissing(),

            self::testClear(),

        ];
    }

    private static function testSegments(): array
    {
        $reflection =
            new ReflectionClass(
                Config::class,
            );

        $method =
            $reflection->getMethod(
                'segments',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                null,
                'app.debug',
            );

        return [
            'name' =>
                'Config segments',

            'success' =>
                $result === [
                    'app',
                    'debug',
                ],
        ];
    }

    private static function testArrayGet(): array
    {
        $reflection =
            new ReflectionClass(
                Config::class,
            );

        $method =
            $reflection->getMethod(
                'arrayGet',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                null,
                [
                    'app' => [
                        'debug' => true,
                    ],
                ],
                [
                    'app',
                    'debug',
                ],
            );

        return [
            'name' =>
                'Config array get',

            'success' =>
                $result === true,
        ];
    }

    private static function testArrayGetDefault(): array
    {
        $reflection =
            new ReflectionClass(
                Config::class,
            );

        $method =
            $reflection->getMethod(
                'arrayGet',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                null,
                [],
                [
                    'missing',
                ],
                'default',
            );

        return [
            'name' =>
                'Config array get default',

            'success' =>
                $result === 'default',
        ];
    }

    private static function testArrayHas(): array
    {
        $reflection =
            new ReflectionClass(
                Config::class,
            );

        $method =
            $reflection->getMethod(
                'arrayHas',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                null,
                [
                    'app' => [
                        'debug' => true,
                    ],
                ],
                [
                    'app',
                    'debug',
                ],
            );

        return [
            'name' =>
                'Config array has',

            'success' =>
                $result === true,
        ];
    }

    private static function testArrayHasMissing(): array
    {
        $reflection =
            new ReflectionClass(
                Config::class,
            );

        $method =
            $reflection->getMethod(
                'arrayHas',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                null,
                [],
                [
                    'missing',
                ],
            );

        return [
            'name' =>
                'Config array has missing',

            'success' =>
                $result === false,
        ];
    }

    private static function testClear(): array
    {
        Config::clear();

        return [
            'name' =>
                'Config clear cache',

            'success' =>
                true,
        ];
    }
}