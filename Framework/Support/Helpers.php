<?php

declare(strict_types=1);

use Framework\Application\App;
use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Container\AppContainer;
use Framework\Http\Response;
use Framework\Support\Session;
use App\Models\User;
use App\Services\Auth\AuthService;

/*
|------------------------------------------------------------------
| app()
|------------------------------------------------------------------
*/

if (! function_exists('app')) {

    function app(
        ?string $abstract = null,
    ): mixed {

        $container =
            AppContainer::get();

        return $abstract === null
            ? $container
            : $container->get($abstract);
    }
}

/*
|------------------------------------------------------------------
| dump()
|------------------------------------------------------------------
*/

if (! function_exists('dump')) {

    function dump(
        mixed ...$vars,
    ): void {

        if (! App::debug()) {
            return;
        }

        echo '
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
        ';

        foreach ($vars as $var) {
            var_dump($var);
        }

        echo '</pre>';
    }
}

/*
|------------------------------------------------------------------
| dd()
|------------------------------------------------------------------
*/

if (! function_exists('dd')) {

    function dd(
        mixed ...$vars,
    ): never {

        dump(...$vars);

        exit;
    }
}

/*
|------------------------------------------------------------------
| Paths
|------------------------------------------------------------------
*/

if (! function_exists('base_path')) {

    function base_path(
        string $path = '',
    ): string {

        $base =
            rtrim(
                ROOT,
                DIRECTORY_SEPARATOR,
            );

        return $path === ''
            ? $base
            : $base
                . DIRECTORY_SEPARATOR
                . ltrim($path, '/\\');
    }
}

if (! function_exists('app_path')) {

    function app_path(
        string $path = '',
    ): string {

        return base_path(
            'App'
            . (
                $path !== ''
                    ? DIRECTORY_SEPARATOR
                        . ltrim($path, '/\\')
                    : ''
            ),
        );
    }
}

if (! function_exists('view_path')) {

    function view_path(
        string $path = '',
    ): string {

        return app_path(
            'Views'
            . (
                $path !== ''
                    ? DIRECTORY_SEPARATOR
                        . ltrim($path, '/\\')
                    : ''
            ),
        );
    }
}

/*
|------------------------------------------------------------------
| redirect()
|------------------------------------------------------------------
*/

if (! function_exists('redirect')) {

    function redirect(
        string $path = '',
        int $status = 302,
    ): never {

        if (
            preg_match(
                '#^https?://#i',
                $path,
            ) === 1
        ) {
            Response::redirect(
                $path,
                $status,
            );
        }

        $baseUri =
            rtrim(
                App::baseUri(),
                '/',
            );

        Response::redirect(
            $baseUri
            . '/'
            . ltrim($path, '/'),
            $status,
        );
    }
}

/*
|------------------------------------------------------------------
| env()
|------------------------------------------------------------------
*/

if (! function_exists('env')) {

    function env(
        string $key,
        mixed $default = null,
    ): mixed {

        return Env::get(
            $key,
            $default,
        );
    }
}

if (! function_exists('env_bool')) {

    function env_bool(
        string $key,
        bool $default = false,
    ): bool {

        return Env::bool(
            $key,
            $default,
        );
    }
}

if (! function_exists('env_int')) {

    function env_int(
        string $key,
        int $default = 0,
    ): int {

        return Env::int(
            $key,
            $default,
        );
    }
}

/*
|------------------------------------------------------------------
| config()
|------------------------------------------------------------------
*/

if (! function_exists('config')) {

    function config(
        string $key,
        mixed $default = null,
    ): mixed {

        return Config::get(
            $key,
            $default,
        );
    }
}

/*
|------------------------------------------------------------------
| Escape HTML
|------------------------------------------------------------------
*/

if (! function_exists('e')) {

    function e(
        mixed $value,
    ): string {

        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES
            | ENT_SUBSTITUTE,
            'UTF-8',
        );
    }
}

/*
|------------------------------------------------------------------
| CSRF
|------------------------------------------------------------------
*/

if (! function_exists('csrf_token')) {

    function csrf_token(): string
    {
        if (! Session::has('csrf_token')) {

            Session::set(
                'csrf_token',
                bin2hex(
                    random_bytes(32),
                ),
            );
        }

        return (string)
            Session::get(
                'csrf_token',
            );
    }
}

if (! function_exists('csrf_field')) {

    function csrf_field(): string
    {
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            e(csrf_token()),
        );
    }
}

if (! function_exists('csrf_meta_tag')) {

    function csrf_meta_tag(): string
    {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            e(csrf_token()),
        );
    }
}

if (! function_exists('session')) {

    function session(
        string $key,
        mixed $default = null,
    ): mixed {

        return Session::get(
            $key,
            $default,
        );
    }
}

if (! function_exists('old')) {

    function old(
        string $key,
        mixed $default = '',
    ): mixed {

        $old =
            Session::get(
                'old',
                [],
            );

        return $old[$key]
            ?? $default;
    }
}

if (! function_exists('errors')) {

    function errors(
        ?string $key = null,
    ): mixed {

        $errors =
            Session::get(
                'errors',
                [],
            );

        if ($key === null) {
            return $errors;
        }

        return $errors[$key]
            ?? null;
    }
}

if (! function_exists('has_error')) {

    function has_error(
        string $key,
    ): bool {

        return errors($key) !== null;
    }
}

if (! function_exists('error_class')) {

    function error_class(
        string $key,
        string $class = 'is-invalid',
    ): string {

        return has_error($key)
            ? $class
            : '';
    }
}

if (! function_exists('base_uri')) {

    function base_uri(): string
    {
        $baseUri = trim(
            App::baseUri(),
            '/',
        );

        return $baseUri !== ''
            ? '/' . $baseUri
            : '';
    }
}

if (! function_exists('view_base_uri')) {

    /**
     * @return non-empty-string
     */
    function view_base_uri(): string
    {
        return rtrim(base_uri(), '/') . '/';
    }
}

/*
|------------------------------------------------------------------
| Auth
|------------------------------------------------------------------
*/

if (! function_exists('auth')) {

    function auth(): AuthService
    {
        return app(
            AuthService::class,
        );
    }
}

if (! function_exists('user')) {

    function user(): ?User
    {
        return auth()->user();
    }
}

if (! function_exists('is_logged')) {

    function is_logged(): bool
    {
        return auth()->check();
    }
}