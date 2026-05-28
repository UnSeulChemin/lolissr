<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\Env;
use ReflectionClass;

final class EnvTest
{
    public static function run(): array
    {
        return [

            self::testCastTrue(),

            self::testCastFalse(),

            self::testCastNull(),

            self::testCastEmpty(),

            self::testBool(),

            self::testInt(),

            self::testHas(),

        ];
    }

    private static function testCastTrue(): array
    {
        $result =
            self::cast(
                'true',
            );

        return [
            'name' =>
                'Env cast true',

            'success' =>
                $result === true,
        ];
    }

    private static function testCastFalse(): array
    {
        $result =
            self::cast(
                'false',
            );

        return [
            'name' =>
                'Env cast false',

            'success' =>
                $result === false,
        ];
    }

    private static function testCastNull(): array
    {
        $result =
            self::cast(
                'null',
            );

        return [
            'name' =>
                'Env cast null',

            'success' =>
                $result === null,
        ];
    }

    private static function testCastEmpty(): array
    {
        $result =
            self::cast(
                'empty',
            );

        return [
            'name' =>
                'Env cast empty',

            'success' =>
                $result === '',
        ];
    }

    private static function testBool(): array
    {
        $_ENV['TEST_BOOL'] =
            'true';

        Env::clear();

        return [
            'name' =>
                'Env bool',

            'success' =>
                Env::bool(
                    'TEST_BOOL',
                ) === true,
        ];
    }

    private static function testInt(): array
    {
        $_ENV['TEST_INT'] =
            '42';

        Env::clear();

        return [
            'name' =>
                'Env int',

            'success' =>
                Env::int(
                    'TEST_INT',
                ) === 42,
        ];
    }

    private static function testHas(): array
    {
        $_ENV['TEST_HAS'] =
            'ok';

        Env::clear();

        return [
            'name' =>
                'Env has',

            'success' =>
                Env::has(
                    'TEST_HAS',
                ),
        ];
    }

    private static function cast(
        string $value,
    ): mixed {
        $reflection =
            new ReflectionClass(
                Env::class,
            );

        $method =
            $reflection->getMethod(
                'cast',
            );

        $method->setAccessible(
            true,
        );

        return $method->invoke(
            null,
            $value,
        );
    }
}