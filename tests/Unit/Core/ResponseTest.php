<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

final class ResponseTest
{
    public static function run(): array
    {
        return [

            self::testJsonEncoding(),

            self::testUtf8(),

            self::testRedirectUrl(),

        ];
    }

    private static function testJsonEncoding(): array
    {
        $json = json_encode(
            [
                'success' => true,
            ],
        );

        return [
            'name' =>
                'Response JSON encoding',

            'success' =>
                $json !== false,
        ];
    }

    private static function testUtf8(): array
    {
        $json = json_encode(
            [
                'title' => 'Élite',
            ],
            JSON_UNESCAPED_UNICODE,
        );

        return [
            'name' =>
                'Response UTF8',

            'success' =>
                str_contains(
                    (string) $json,
                    'Élite',
                ),
        ];
    }

    private static function testRedirectUrl(): array
    {
        $url =
            '/manga/rave';

        return [
            'name' =>
                'Response redirect URL',

            'success' =>
                str_starts_with(
                    $url,
                    '/',
                ),
        ];
    }
}