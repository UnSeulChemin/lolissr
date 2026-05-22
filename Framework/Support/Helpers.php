<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use Framework\Application\App;
use Framework\Config\Config;
use Framework\Config\Env;
use Framework\Container\AppContainer;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Support\Session;

/*
|--------------------------------------------------------------------------
| app()
|--------------------------------------------------------------------------
*/

if (!function_exists('app')) {
    function app(
        ?string $abstract = null,
    ): mixed {
        $container = AppContainer::get();

        if ($abstract === null) {
            return $container;
        }

        return $container->get($abstract);
    }
}

/*
|--------------------------------------------------------------------------
| dump()
|--------------------------------------------------------------------------
*/

if (!function_exists('dump')) {
    function dump(
        mixed ...$vars,
    ): void {
        if (!App::debug()) {
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
|--------------------------------------------------------------------------
| dd()
|--------------------------------------------------------------------------
*/

if (!function_exists('dd')) {
    function dd(
        mixed ...$vars,
    ): never {
        if (!App::debug()) {
            http_response_code(500);

            exit;
        }

        dump(...$vars);

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Paths
|--------------------------------------------------------------------------
*/

if (!function_exists('base_path')) {
    function base_path(
        string $path = '',
    ): string {
        $base = rtrim(
            ROOT,
            DIRECTORY_SEPARATOR,
        );

        if ($path === '') {
            return $base;
        }

        return $base
            . DIRECTORY_SEPARATOR
            . ltrim($path, '/\\');
    }
}

if (!function_exists('app_path')) {
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

if (!function_exists('view_path')) {
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
|--------------------------------------------------------------------------
| redirect()
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {
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

        $baseUri = rtrim(
            App::baseUri(),
            '/',
        );

        $url = $baseUri
            . '/'
            . ltrim($path, '/');

        Response::redirect(
            $url,
            $status,
        );
    }
}

/*
|--------------------------------------------------------------------------
| json()
|--------------------------------------------------------------------------
*/

if (!function_exists('json')) {
    /**
     * @param array<string, mixed> $data
     */
    function json(
        array $data,
        int $status = 200,
    ): never {
        Response::json(
            $data,
            $status,
        );
    }
}

/*
|--------------------------------------------------------------------------
| view()
|--------------------------------------------------------------------------
*/

if (!function_exists('view')) {
    /**
     * @param array<string, mixed> $data
     */
    function view(
        string $viewFile,
        array $data = [],
        ?string $title = null,
    ): never {
        $title ??= App::siteName();

        $view = $data;

        $baseUri = App::baseUri();

        /** @var Request $request */
        $request = app(Request::class);

        $currentPath = $request->path();

        $viewPath = view_path(
            $viewFile . '.php',
        );

        $layoutPath = view_path(
            'layouts/base.php',
        );

        if (!is_file($viewPath)) {
            throw new RuntimeException(
                'Vue introuvable : '
                . $viewFile,
            );
        }

        if (!is_file($layoutPath)) {
            throw new RuntimeException(
                'Layout introuvable : layouts/base',
            );
        }

        extract(
            $view,
            EXTR_SKIP,
        );

        ob_start();

        require $viewPath;

        $content = ob_get_clean() ?: '';

        ob_start();

        require $layoutPath;

        $html = ob_get_clean() ?: '';

        Response::html($html);
    }
}

/*
|--------------------------------------------------------------------------
| env()
|--------------------------------------------------------------------------
*/

if (!function_exists('env')) {
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

if (!function_exists('env_bool')) {
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

if (!function_exists('env_int')) {
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
|--------------------------------------------------------------------------
| config()
|--------------------------------------------------------------------------
*/

if (!function_exists('config')) {
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
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {
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
|--------------------------------------------------------------------------
| AJAX
|--------------------------------------------------------------------------
*/

if (!function_exists('is_ajax')) {
    function is_ajax(): bool
    {
        /** @var Request $request */
        $request = app(Request::class);

        return $request->isAjax();
    }
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set(
                'csrf_token',
                bin2hex(
                    random_bytes(32),
                ),
            );
        }

        return (string) Session::get(
            'csrf_token',
        );
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            e(csrf_token()),
        );
    }
}

if (!function_exists('csrf_meta_tag')) {
    function csrf_meta_tag(): string
    {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            e(csrf_token()),
        );
    }
}