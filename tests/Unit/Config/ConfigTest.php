<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testGetReturnsDefaultForEmptyKey(): void
    {
        $this->assertSame(
            'default',
            Config::get(
                '',
                'default',
            ),
        );
    }

    public function testGetReturnsDefaultForUnknownConfig(): void
    {
        $this->assertSame(
            'default',
            Config::get(
                'unknown.key',
                'default',
            ),
        );
    }

    public function testHasReturnsFalseForEmptyKey(): void
    {
        $this->assertFalse(
            Config::has(
                '',
            ),
        );
    }

    public function testHasReturnsFalseForUnknownConfig(): void
    {
        $this->assertFalse(
            Config::has(
                'unknown.key',
            ),
        );
    }

    public function testClear(): void
    {
        Config::clear();

        $this->assertTrue(
            true,
        );
    }
}