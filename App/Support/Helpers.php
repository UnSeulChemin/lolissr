<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Auth\AuthService;

// =========================================
// CHEMINS
// =========================================

if (! function_exists('app_path'))
{
    function app_path(string $path = ''): string
    {
        $appPath = base_path('App');

        return $path === ''
            ? $appPath
            : $appPath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}

if (! function_exists('view_path'))
{
    function view_path(string $path = ''): string
    {
        $viewPath = app_path('Views');

        return $path === ''
            ? $viewPath
            : $viewPath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}

// =========================================
// AUTHENTIFICATION
// =========================================

if (! function_exists('auth'))
{
    function auth(): AuthService
    {
        $auth = app(AuthService::class);

        if (! $auth instanceof AuthService)
        {
            throw new RuntimeException('AuthService non disponible.');
        }

        return $auth;
    }
}

if (! function_exists('user'))
{
    function user(): ?User
    {
        return auth()->user();
    }
}

if (! function_exists('is_logged'))
{
    function is_logged(): bool
    {
        return auth()->check();
    }
}