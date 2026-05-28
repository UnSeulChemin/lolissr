<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\Env;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class EnvTest extends TestCase
{
    public function testCastTrue(): void
    {
        $this->assertTrue(
            $this->cast(
                'true',
            ),
        );
    }

    public function testCastFalse(): void
    {
        $this->assertFalse(
            $this->cast(
                'false',
            ),
        );
    }

    public function testCastNull(): void
    {
        $this->assertNull(
            $this->cast(
                'null',
            ),
        );
    }

    public function testCastEmpty(): void
    {
        $this->assertSame(
            '',
            $this->cast(
                'empty',
            ),
        );
    }

    public function testBool(): void
    {
        $_ENV['TEST_BOOL'] =
            'true';

        Env::clear();

        $this->assertTrue(
            Env::bool(
                'TEST_BOOL',
            ),
        );
    }

    public function testInt(): void
    {
        $_ENV['TEST_INT'] =
            '42';

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
        $_ENV['TEST_HAS'] =
            'ok';

        Env::clear();

        $this->assertTrue(
            Env::has(
                'TEST_HAS',
            ),
        );
    }

    private function cast(
        string $value,
    ): mixed {

        return $this->privateMethod(
            'cast',
        )->invoke(
            null,
            $value,
        );
    }

    private function privateMethod(
        string $method,
    ): ReflectionMethod {

        $reflection =
            new ReflectionClass(
                Env::class,
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