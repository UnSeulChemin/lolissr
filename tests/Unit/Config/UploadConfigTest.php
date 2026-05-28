<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\UploadConfig;
use ReflectionClass;

final class UploadConfigTest
{
    public static function run(): array
    {
        return [

            self::testNormalizedList(),

            self::testMaxSize(),

            self::testAllowedExtensions(),

            self::testAllowedMimeTypes(),

            self::testThumbnailDirectory(),

        ];
    }

    private static function testNormalizedList(): array
    {
        $reflection =
            new ReflectionClass(
                UploadConfig::class,
            );

        $method =
            $reflection->getMethod(
                'normalizedList',
            );

        $method->setAccessible(
            true,
        );

        $result =
            $method->invoke(
                null,
                [
                    ' JPG ',
                    'png',
                    'jpg',
                    '',
                ],
            );

        return [
            'name' =>
                'UploadConfig normalized list',

            'success' =>
                $result === [
                    'jpg',
                    'png',
                ],
        ];
    }

    private static function testMaxSize(): array
    {
        $result =
            UploadConfig::maxSize();

        return [
            'name' =>
                'UploadConfig max size',

            'success' =>
                $result >= 1,
        ];
    }

    private static function testAllowedExtensions(): array
    {
        $result =
            UploadConfig::allowedExtensions();

        return [
            'name' =>
                'UploadConfig allowed extensions',

            'success' =>
                is_array($result),
        ];
    }

    private static function testAllowedMimeTypes(): array
    {
        $result =
            UploadConfig::allowedMimeTypes();

        return [
            'name' =>
                'UploadConfig allowed mime types',

            'success' =>
                is_array($result),
        ];
    }

    private static function testThumbnailDirectory(): array
    {
        $result =
            UploadConfig::mangaThumbnailDirectory();

        return [
            'name' =>
                'UploadConfig thumbnail directory',

            'success' =>
                $result !== '',
        ];
    }
}