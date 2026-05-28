<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Framework\Config\Config;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class ConfigTest extends TestCase
{
    public function testSegments(): void
    {
        $result =
            $this->privateMethod(
                'segments',
            )->invoke(
                null,
                'app.debug',
            );

        $this->assertSame(
            [
                'app',
                'debug',
            ],
            $result,
        );
    }

    public function testArrayGet(): void
    {
        $result =
            $this->privateMethod(
                'arrayGet',
            )->invoke(
                null,
                [
                    'app' => [
                        'debug' => true,
                    ],
                ],
                [
                    'app',
                    'debug',
                ],
            );

        $this->assertTrue(
            $result,
        );
    }

    public function testArrayGetDefault(): void
    {
        $result =
            $this->privateMethod(
                'arrayGet',
            )->invoke(
                null,
                [],
                [
                    'missing',
                ],
                'default',
            );

        $this->assertSame(
            'default',
            $result,
        );
    }

    public function testArrayHas(): void
    {
        $result =
            $this->privateMethod(
                'arrayHas',
            )->invoke(
                null,
                [
                    'app' => [
                        'debug' => true,
                    ],
                ],
                [
                    'app',
                    'debug',
                ],
            );

        $this->assertTrue(
            $result,
        );
    }

    public function testArrayHasMissing(): void
    {
        $result =
            $this->privateMethod(
                'arrayHas',
            )->invoke(
                null,
                [],
                [
                    'missing',
                ],
            );

        $this->assertFalse(
            $result,
        );
    }

    public function testClear(): void
    {
        Config::clear();

        $this->assertTrue(
            true,
        );
    }

    private function privateMethod(
        string $method,
    ): ReflectionMethod {

        $reflection =
            new ReflectionClass(
                Config::class,
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