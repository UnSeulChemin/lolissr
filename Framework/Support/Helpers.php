<?php

declare(strict_types=1);

use Framework\Application\App;
use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Container\AppContainer;
use Framework\Http\Response;
use Framework\Support\Session;

// =========================================
// CONTAINER
// =========================================

if (! function_exists('app'))
{
    function app(?string $abstract = null): mixed
    {
        $container = AppContainer::get();

        return $abstract === null
            ? $container
            : $container->get($abstract);
    }
}

// =========================================
// DEBUG
// =========================================

if (! function_exists('dump'))
{
    function dump(mixed ...$variables): void
    {
        if (! App::debug())
        {
            return;
        }

        echo <<<'HTML'
        <pre style="
            background:#222;
            color:#fff;
            padding:15px;
            font-size:14px;
            line-height:1.4;
            overflow:auto;
            white-space:pre-wrap;
            border-radius:8px;
        ">
        HTML;

        foreach ($variables as $variable)
        {
            var_dump($variable);
        }

        echo '</pre>';
    }
}

if (! function_exists('dd'))
{
    function dd(mixed ...$variables): never
    {
        dump(...$variables);

        exit;
    }
}

// =========================================
// CHEMINS
// =========================================

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

// =========================================
// REDIRECTION
// =========================================

if (! function_exists('redirect'))
{
    function redirect(string $path = '', int $status = 302): never
    {
        if (preg_match('#^https?://#i', $path) === 1)
        {
            Response::redirect($path, $status);
        }

        Response::redirect(view_base_uri() . ltrim($path, '/'), $status);
    }
}

// =========================================
// ENVIRONNEMENT
// =========================================

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

// =========================================
// CONFIGURATION
// =========================================

if (! function_exists('config'))
{
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

// =========================================
// HTML
// =========================================

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

// =========================================
// CSRF
// =========================================

if (! function_exists('csrf_token'))
{
    function csrf_token(): string
    {
        $token = Session::get('csrf_token');

        if (! is_string($token) || $token === '')
        {
            $token = bin2hex(random_bytes(32));

            Session::set('csrf_token', $token);
        }

        return $token;
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

// =========================================
// SESSION
// =========================================

if (! function_exists('session'))
{
    function session(string $key, mixed $default = null): mixed
    {
        return Session::get($key, $default);
    }
}

if (! function_exists('old'))
{
    function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::get('old', []);

        if (! is_array($old))
        {
            return $default;
        }

        return array_key_exists($key, $old)
            ? $old[$key]
            : $default;
    }
}

if (! function_exists('errors'))
{
    /**
     * @return array<string, string>|string|null
     */
    function errors(?string $key = null): array|string|null
    {
        $errors = Session::get('errors', []);

        if (! is_array($errors))
        {
            return $key === null ? [] : null;
        }

        /** @var array<string, string> $errors */
        return $key === null
            ? $errors
            : ($errors[$key] ?? null);
    }
}

if (! function_exists('has_error'))
{
    function has_error(string $key): bool
    {
        return errors($key) !== null;
    }
}

if (! function_exists('error_class'))
{
    function error_class(string $key, string $class = 'is-invalid'): string
    {
        return has_error($key) ? $class : '';
    }
}

// =========================================
// BASE URI
// =========================================

if (! function_exists('base_uri'))
{
    function base_uri(): string
    {
        static $baseUri;

        $baseUri ??= trim(App::baseUri(), '/');

        return $baseUri !== '' ? '/' . $baseUri : '';
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