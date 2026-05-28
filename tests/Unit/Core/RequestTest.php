<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Http\Request;

final class RequestTest
{
    public static function run(): array
    {
        return [

            self::testMethod(),

            self::testAjax(),

            self::testPrefetch(),

            self::testPartial(),

            self::testExpectsJson(),

            self::testPath(),

            self::testInput(),

            self::testFiles(),

        ];
    }

    private static function testMethod(): array
    {
        $request = new Request(
            server: [
                'REQUEST_METHOD' => 'POST',
            ],
        );

        return [
            'name' =>
                'Request method POST',

            'success' =>
                $request->method() === 'POST'
                && $request->isPost()
                && !$request->isGet(),
        ];
    }

    private static function testAjax(): array
    {
        $request = new Request(
            server: [
                'HTTP_X_REQUESTED_WITH' =>
                    'XMLHttpRequest',
            ],
        );

        return [
            'name' =>
                'Request AJAX detection',

            'success' =>
                $request->isAjax(),
        ];
    }

    private static function testPrefetch(): array
    {
        $request = new Request(
            server: [
                'HTTP_PURPOSE' =>
                    'prefetch',
            ],
        );

        return [
            'name' =>
                'Request prefetch detection',

            'success' =>
                $request->isPrefetch(),
        ];
    }

    private static function testPartial(): array
    {
        $request = new Request(
            server: [
                'HTTP_X_PARTIAL' =>
                    'true',
            ],
        );

        return [
            'name' =>
                'Request partial detection',

            'success' =>
                $request->wantsPartial(),
        ];
    }

    private static function testExpectsJson(): array
    {
        $request = new Request(
            server: [
                'HTTP_ACCEPT' =>
                    'application/json',
            ],
        );

        return [
            'name' =>
                'Request expects JSON',

            'success' =>
                $request->expectsJson(),
        ];
    }

    private static function testPath(): array
    {
        $request = new Request(
            server: [
                'REQUEST_URI' =>
                    '/recherche/rave',
            ],
        );

        return [
            'name' =>
                'Request clean search path',

            'success' =>
                $request->path()
                === '/recherche/rave',
        ];
    }

    private static function testInput(): array
    {
        $request = new Request(
            get: [
                'search' =>
                    'rave',
            ],
        );

        return [
            'name' =>
                'Request input query',

            'success' =>
                $request->input(
                    'search',
                ) === 'rave',
        ];
    }

    private static function testFiles(): array
    {
        $request = new Request(
            files: [
                'image' => [
                    'name' =>
                        'cover.jpg',
                ],
            ],
        );

        return [
            'name' =>
                'Request files',

            'success' =>
                is_array(
                    $request->file(
                        'image',
                    ),
                ),
        ];
    }
}