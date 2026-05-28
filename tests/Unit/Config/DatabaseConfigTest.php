<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\DatabaseConfig;
use PHPUnit\Framework\TestCase;

final class DatabaseConfigTest extends TestCase
{
    public function testHost(): void
    {
        $this->assertIsString(
            DatabaseConfig::host(),
        );
    }

    public function testPort(): void
    {
        $this->assertGreaterThanOrEqual(
            1,
            DatabaseConfig::port(),
        );
    }

    public function testName(): void
    {
        $this->assertIsString(
            DatabaseConfig::name(),
        );
    }

    public function testUser(): void
    {
        $this->assertIsString(
            DatabaseConfig::user(),
        );
    }

    public function testPass(): void
    {
        $this->assertIsString(
            DatabaseConfig::pass(),
        );
    }

    public function testCharset(): void
    {
        $this->assertIsString(
            DatabaseConfig::charset(),
        );
    }
}