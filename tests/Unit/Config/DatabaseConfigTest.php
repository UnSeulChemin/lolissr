<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\DatabaseConfig;

final class DatabaseConfigTest
{
    public static function run(): array
    {
        return [

            self::testHost(),

            self::testPort(),

            self::testCharset(),

        ];
    }

    private static function testHost(): array
    {
        $host =
            DatabaseConfig::host();

        return [
            'name' =>
                'DatabaseConfig host',

            'success' =>
                is_string($host)
                && $host !== '',
        ];
    }

    private static function testPort(): array
    {
        $port =
            DatabaseConfig::port();

        return [
            'name' =>
                'DatabaseConfig port',

            'success' =>
                $port >= 1,
        ];
    }

    private static function testCharset(): array
    {
        $charset =
            DatabaseConfig::charset();

        return [
            'name' =>
                'DatabaseConfig charset',

            'success' =>
                $charset !== '',
        ];
    }
}