<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\DatabaseConfig;
use PHPUnit\Framework\TestCase;

final class DatabaseConfigTest extends TestCase
{
    public function testHost(): void
    {
        $host =
            DatabaseConfig::host();

        $this->assertIsString(
            $host,
        );

        $this->assertNotSame(
            '',
            $host,
        );
    }

    public function testPort(): void
    {
        $this->assertGreaterThanOrEqual(
            1,
            DatabaseConfig::port(),
        );
    }

    public function testCharset(): void
    {
        $charset =
            DatabaseConfig::charset();

        $this->assertIsString(
            $charset,
        );

        $this->assertNotSame(
            '',
            $charset,
        );
    }
}