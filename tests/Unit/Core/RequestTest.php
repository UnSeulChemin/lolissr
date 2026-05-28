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

            self::testDefaultMethod(),

            self::testAjax(),

            self::testAjaxViaXAjax(),

            self::testPrefetch(),

            self::testPrefetchViaHeader(),

            self::testPartial(),

            self::testExpectsJson(),

            self::testPath(),

            self::testRootPath(),

            self::testInputPriority(),

            self::testInputDefault(),

            self::testQueryAll(),

            self::testServer(),

            self::testServerDefault(),

            self::testHeader(),

            self::testFiles(),

            self::testMissingFile(),

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
                && ! $request->isGet(),
        ];
    }

    private static function testDefaultMethod(): array
    {
        $request = new Request();

        return [
            'name' =>
                'Request default method',

            'success' =>
                $request->method() === 'GET',
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

    private static function testAjaxViaXAjax(): array
    {
        $request = new Request(
            server: [
                'HTTP_X_AJAX' =>
                    'true',
            ],
        );

        return [
            'name' =>
                'Request AJAX X-Ajax',

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
                'Request prefetch purpose',

            'success' =>
                $request->isPrefetch(),
        ];
    }

    private static function testPrefetchViaHeader(): array
    {
        $request = new Request(
            server: [
                'HTTP_X_PREFETCH' =>
                    'true',
            ],
        );

        return [
            'name' =>
                'Request prefetch X-Prefetch',

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
                    '/recherche/rave?test=1',
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

    private static function testRootPath(): array
    {
        $request = new Request();

        return [
            'name' =>
                'Request root path',

            'success' =>
                $request->path()
                    === '/',
        ];
    }

    private static function testInputPriority(): array
    {
        $request = new Request(
            get: [
                'value' => 'get',
            ],
            post: [
                'value' => 'post',
            ],
        );

        return [
            'name' =>
                'Request input priority',

            'success' =>
                $request->input(
                    'value',
                ) === 'post',
        ];
    }

    private static function testInputDefault(): array
    {
        $request = new Request();

        return [
            'name' =>
                'Request input default',

            'success' =>
                $request->input(
                    'missing',
                    'default',
                ) === 'default',
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

    private static function testServerDefault(): array
    {
        $request = new Request();

        return [
            'name' =>
                'Request server default',

            'success' =>
                $request->server(
                    'missing',
                    'fallback',
                ) === 'fallback',
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

    private static function testMissingFile(): array
    {
        $request = new Request();

        return [
            'name' =>
                'Request missing file',

            'success' =>
                $request->file(
                    'missing',
                ) === null,
        ];
    }
}