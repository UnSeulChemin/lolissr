<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\UploadConfig;
use PHPUnit\Framework\TestCase;

final class UploadConfigTest extends TestCase
{
    public function testMaxSize(): void
    {
        $this->assertGreaterThanOrEqual(
            1,
            UploadConfig::maxSize(),
        );
    }

    public function testAllowedExtensionsReturnsArray(): void
    {
        $this->assertIsArray(
            UploadConfig::allowedExtensions(),
        );
    }

    public function testAllowedMimeTypesReturnsArray(): void
    {
        $this->assertIsArray(
            UploadConfig::allowedMimeTypes(),
        );
    }

    public function testThumbnailDirectoryReturnsString(): void
    {
        $this->assertNotSame(
            '',
            UploadConfig::mangaThumbnailDirectory(),
        );
    }

    public function testThumbnailDirectoryEndsWithSeparator(): void
    {
        $this->assertStringEndsWith(
            DIRECTORY_SEPARATOR,
            UploadConfig::mangaThumbnailDirectory(),
        );
    }
}