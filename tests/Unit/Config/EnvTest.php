<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    protected function setUp(): void
    {
        Env::clear();
    }

    public function testGetReturnsDefaultForEmptyKey(): void
    {
        $this->assertSame(
            'default',
            Env::get(
                '',
                'default',
            ),
        );
    }

    public function testBool(): void
    {
        $_ENV['TEST_BOOL'] = 'true';

        Env::clear();

        $this->assertTrue(
            Env::bool(
                'TEST_BOOL',
            ),
        );
    }

    public function testInt(): void
    {
        $_ENV['TEST_INT'] = '42';

        Env::clear();

        $this->assertSame(
            42,
            Env::int(
                'TEST_INT',
            ),
        );
    }

    public function testHas(): void
    {
        $_ENV['TEST_HAS'] = 'ok';

        Env::clear();

        $this->assertTrue(
            Env::has(
                'TEST_HAS',
            ),
        );
    }

    public function testMissingValueReturnsDefault(): void
    {
        Env::clear();

        $this->assertSame(
            'fallback',
            Env::get(
                'UNKNOWN_ENV_KEY',
                'fallback',
            ),
        );
    }
}