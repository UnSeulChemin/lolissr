<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\UploadConfig;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class UploadConfigTest extends TestCase
{
    public function testNormalizedList(): void
    {
        $result =
            $this->privateMethod(
                'normalizedList',
            )->invoke(
                null,
                [
                    ' JPG ',
                    'png',
                    'jpg',
                    '',
                ],
            );

        $this->assertSame(
            [
                'jpg',
                'png',
            ],
            $result,
        );
    }

    public function testMaxSize(): void
    {
        $this->assertGreaterThanOrEqual(
            1,
            UploadConfig::maxSize(),
        );
    }

    public function testAllowedExtensions(): void
    {
        $this->assertIsArray(
            UploadConfig::allowedExtensions(),
        );
    }

    public function testAllowedMimeTypes(): void
    {
        $this->assertIsArray(
            UploadConfig::allowedMimeTypes(),
        );
    }

    public function testThumbnailDirectory(): void
    {
        $directory =
            UploadConfig::mangaThumbnailDirectory();

        $this->assertNotSame(
            '',
            $directory,
        );
    }

    private function privateMethod(
        string $method,
    ): ReflectionMethod {

        $reflection =
            new ReflectionClass(
                UploadConfig::class,
            );

        $method =
            $reflection->getMethod(
                $method,
            );

        $method->setAccessible(
            true,
        );

        return $method;
    }
}