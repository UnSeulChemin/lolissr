<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Auth\AuthService;

use Framework\Auth\AuthenticationInterface;
use Framework\Container\Container;

final class AppServiceProvider
{
    private function __construct()
    {
    }

    // =========================================
    // SERVICES
    // =========================================

    public static function register(Container $container): void
    {
        $container->singleton(AuthService::class);

        $container->singleton(
            AuthenticationInterface::class,
            static fn (Container $container): object => $container->get(AuthService::class)
        );
    }
}