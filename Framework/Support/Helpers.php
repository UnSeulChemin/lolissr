<?php

declare(strict_types=1);

use Framework\Config\ApplicationConfig;
use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Container\ContainerRegistry;
use Framework\Http\Session;

// =================================================
// CONTENEUR
// =================================================

if (! function_exists('app'))
{
    function app(?string $abstract = null): mixed
    {
        $container = ContainerRegistry::get();

        return $abstract === null
            ? $container
            : $container->get($abstract);
    }
}

// =================================================
// CHEMINS
// =================================================

if (! function_exists('base_path'))
{
    function base_path(string $path = ''): string
    {
        static $basePath;

        $basePath ??= rtrim(ROOT, '/\\');

        return $path === ''
            ? $basePath
            : $basePath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}

// =================================================
// ENVIRONNEMENT
// =================================================

if (! function_exists('env'))
{
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (! function_exists('env_bool'))
{
    function env_bool(string $key, bool $default = false): bool
    {
        return Env::bool($key, $default);
    }
}

if (! function_exists('env_int'))
{
    function env_int(string $key, int $default = 0): int
    {
        return Env::int($key, $default);
    }
}

// =================================================
// CONFIGURATION
// =================================================

if (! function_exists('config'))
{
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

// =================================================
// HTML
// =================================================

if (! function_exists('e'))
{
    function e(mixed $value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

// =================================================
// CSRF
// =================================================

if (! function_exists('csrf_token'))
{
    function csrf_token(): string
    {
        return Session::withLock(static function (): string {
            $token = Session::get('csrf_token');

            if (! is_string($token) || $token === '')
            {
                $token = bin2hex(random_bytes(32));

                Session::set('csrf_token', $token);
            }

            return $token;
        });
    }
}

if (! function_exists('csrf_field'))
{
    function csrf_field(): string
    {
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            e(csrf_token())
        );
    }
}

if (! function_exists('csrf_meta_tag'))
{
    function csrf_meta_tag(): string
    {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            e(csrf_token())
        );
    }
}

// =================================================
// URI DE BASE
// =================================================

if (! function_exists('base_uri'))
{
    function base_uri(): string
    {
        $baseUri = trim(ApplicationConfig::baseUri(), '/');

        return $baseUri !== ''
            ? '/' . $baseUri
            : '';
    }
}

if (! function_exists('view_base_uri'))
{
    /**
     * @return non-empty-string
     */
    function view_base_uri(): string
    {
        return rtrim(base_uri(), '/') . '/';
    }
}
