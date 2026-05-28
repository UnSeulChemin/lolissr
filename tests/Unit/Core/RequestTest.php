<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testMethod(): void
    {
        $request =
            new Request(
                server: [
                    'REQUEST_METHOD' => 'POST',
                ],
            );

        $this->assertSame(
            'POST',
            $request->method(),
        );

        $this->assertTrue(
            $request->isPost(),
        );

        $this->assertFalse(
            $request->isGet(),
        );
    }

    public function testAjax(): void
    {
        $request =
            new Request(
                server: [
                    'HTTP_X_REQUESTED_WITH' =>
                        'XMLHttpRequest',
                ],
            );

        $this->assertTrue(
            $request->isAjax(),
        );
    }

    public function testPrefetch(): void
    {
        $request =
            new Request(
                server: [
                    'HTTP_PURPOSE' =>
                        'prefetch',
                ],
            );

        $this->assertTrue(
            $request->isPrefetch(),
        );
    }

    public function testPartial(): void
    {
        $request =
            new Request(
                server: [
                    'HTTP_X_PARTIAL' =>
                        'true',
                ],
            );

        $this->assertTrue(
            $request->wantsPartial(),
        );
    }

    public function testExpectsJson(): void
    {
        $request =
            new Request(
                server: [
                    'HTTP_ACCEPT' =>
                        'application/json',
                ],
            );

        $this->assertTrue(
            $request->expectsJson(),
        );
    }

    public function testPath(): void
    {
        $request =
            new Request(
                server: [
                    'REQUEST_URI' =>
                        '/recherche/rave',
                ],
            );

        $this->assertSame(
            '/recherche/rave',
            $request->path(),
        );
    }

    public function testInput(): void
    {
        $request =
            new Request(
                get: [
                    'search' =>
                        'rave',
                ],
            );

        $this->assertSame(
            'rave',
            $request->input(
                'search',
            ),
        );
    }

    public function testFiles(): void
    {
        $request =
            new Request(
                files: [
                    'image' => [
                        'name' =>
                            'cover.jpg',
                    ],
                ],
            );

        $this->assertIsArray(
            $request->file(
                'image',
            ),
        );
    }
}