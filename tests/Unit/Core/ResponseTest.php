<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Framework\Http\Response;
use ReflectionClass;

final class ResponseTest
{
    public static function run(): array
    {
        return [

            self::testClassExists(),

            self::testHtmlMethodExists(),

            self::testJsonMethodExists(),

            self::testRedirectMethodExists(),

            self::testJsonFlags(),

        ];
    }

    private static function testClassExists(): array
    {
        return [
            'name' =>
                'Response class exists',

            'success' =>
                class_exists(
                    Response::class,
                ),
        ];
    }

    private static function testHtmlMethodExists(): array
    {
        return [
            'name' =>
                'Response html method exists',

            'success' =>
                method_exists(
                    Response::class,
                    'html',
                ),
        ];
    }

    private static function testJsonMethodExists(): array
    {
        return [
            'name' =>
                'Response json method exists',

            'success' =>
                method_exists(
                    Response::class,
                    'json',
                ),
        ];
    }

    private static function testRedirectMethodExists(): array
    {
        return [
            'name' =>
                'Response redirect method exists',

            'success' =>
                method_exists(
                    Response::class,
                    'redirect',
                ),
        ];
    }

    private static function testJsonFlags(): array
    {
        $reflection =
            new ReflectionClass(
                Response::class,
            );

        $success =
            $reflection->hasMethod(
                'json',
            );

        return [
            'name' =>
                'Response json method available',

            'success' =>
                $success,
        ];
    }
}