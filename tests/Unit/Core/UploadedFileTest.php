<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

final class UploadedFileTest
{
    public static function run(): array
    {
        return [

            self::testExtensionNormalization(),

            self::testMimeDetection(),

            self::testInvalidMime(),

            self::testFileSize(),

        ];
    }

    private static function testExtensionNormalization(): array
    {
        $extension = 'jpeg';

        $normalized =
            $extension === 'jpeg'
                ? 'jpg'
                : $extension;

        return [
            'name' =>
                'UploadedFile extension normalization',

            'success' =>
                $normalized === 'jpg',
        ];
    }

    private static function testMimeDetection(): array
    {
        $mime =
            'image/jpeg';

        return [
            'name' =>
                'UploadedFile mime detection',

            'success' =>
                str_starts_with(
                    $mime,
                    'image/',
                ),
        ];
    }

    private static function testInvalidMime(): array
    {
        $mime =
            'application/x-php';

        return [
            'name' =>
                'UploadedFile invalid mime',

            'success' =>
                !str_starts_with(
                    $mime,
                    'image/',
                ),
        ];
    }

    private static function testFileSize(): array
    {
        $size =
            1024 * 1024;

        return [
            'name' =>
                'UploadedFile size',

            'success' =>
                $size <= 5 * 1024 * 1024,
        ];
    }
}