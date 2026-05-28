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

            self::testQueryAll(),

            self::testServer(),

            self::testHeader(),

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
                'Request clean path',

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
                'Request input',

            'success' =>
                $request->input(
                    'search',
                ) === 'rave',
        ];
    }

    private static function testQueryAll(): array
    {
        $request = new Request(
            get: [
                'search' =>
                    'rave',
            ],
        );

        return [
            'name' =>
                'Request query all',

            'success' =>
                $request->queryAll()['search']
                    === 'rave',
        ];
    }

    private static function testServer(): array
    {
        $request = new Request(
            server: [
                'HTTP_HOST' =>
                    'localhost',
            ],
        );

        return [
            'name' =>
                'Request server',

            'success' =>
                $request->server(
                    'HTTP_HOST',
                ) === 'localhost',
        ];
    }

    private static function testHeader(): array
    {
        $request = new Request(
            server: [
                'HTTP_X_CUSTOM' =>
                    'test',
            ],
        );

        return [
            'name' =>
                'Request header',

            'success' =>
                $request->header(
                    'X-Custom',
                ) === 'test',
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