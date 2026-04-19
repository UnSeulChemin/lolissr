<?php

declare(strict_types=1);

namespace App\Controllers;

final class TestRouterController
{
    public static array $called = [];

    public static function reset(): void
    {
        self::$called = [];
    }

    public function index(): void
    {
        self::$called = [
            'method' => 'index',
            'params' => [],
        ];
    }

    public function show(string $slug, string $numero): void
    {
        self::$called = [
            'method' => 'show',
            'params' => [$slug, $numero],
        ];
    }

    public function store(): void
    {
        self::$called = [
            'method' => 'store',
            'params' => [],
        ];
    }
}