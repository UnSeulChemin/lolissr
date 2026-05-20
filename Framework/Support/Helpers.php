<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Core\Application\App;
use App\Core\Config\Config;
use App\Core\Config\Env;
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
    function app(?string $abstract = null): mixed
    {
        $container = AppContainer::get();

        return $abstract !== null
            ? $container->get($abstract)
            : $container;
    }
}

/*
|--------------------------------------------------------------------------
| dump()
|--------------------------------------------------------------------------
*/

if (!function_exists('dump')) {
    function dump(mixed ...$vars): void
    {
        if (!env_bool('APP_DEBUG')) {
            return;
        }

        echo '<pre style="
            background:#222;
            color:#fff;
            padding:15px;
            font-size:14px;
            line-height:1.4;
            overflow:auto;
            white-space:pre-wrap;
            border-radius:8px;
        ">';

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
    function dd(mixed ...$vars): never
    {
        if (!env_bool('APP_DEBUG')) {
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
    function base_path(): string
    {
        return App::basePath();
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return rtrim(ROOT, DIRECTORY_SEPARATOR)
            . (
                $path !== ''
                    ? DIRECTORY_SEPARATOR . ltrim($path, '/\\')
                    : ''
            );
    }
}

if (!function_exists('view_path')) {
    function view_path(string $view = ''): string
    {
        return app_path('App/Views')
            . (
                $view !== ''
                    ? DIRECTORY_SEPARATOR . ltrim($view, '/\\')
                    : ''
            );
    }
}

/*
|--------------------------------------------------------------------------
| abort()
|--------------------------------------------------------------------------
*/

if (!function_exists('abort')) {
    function abort(int $code = 404): never
    {
        $controller = app(
            ErrorController::class,
        );

        match ($code) {
            404 => $controller->notFound(),
            405 => $controller->methodNotAllowed(),
            419 => $controller->renderCsrfExpiredPage(),
            default => $controller->serverError(),
        };

        exit;
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
            preg_match('#^https?://#i', $path) === 1
        ) {
            Response::redirect(
                $path,
                $status,
            );
        }

        $url = rtrim(
            base_path(),
            '/',
        ) . '/' . ltrim($path, '/');

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
        Response::json($data, $status);
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

        $viewPath = view_path(
            $viewFile . '.php',
        );

        $layoutPath = view_path(
            'layouts/base.php',
        );

        if (!is_file($viewPath)) {
            throw new \RuntimeException(
                'Vue introuvable : '
                . $viewFile,
            );
        }

        if (!is_file($layoutPath)) {
            throw new \RuntimeException(
                'Layout introuvable : layouts/base',
            );
        }

        ob_start();

        require $viewPath;

        $content = ob_get_clean();

        if ($content === false) {
            $content = '';
        }

        ob_start();

        require $layoutPath;

        $html = ob_get_clean();

        if ($html === false) {
            $html = '';
        }

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
        return (int) Env::get(
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
            ENT_QUOTES | ENT_SUBSTITUTE,
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
        return app(Request::class)
            ->isAjax();
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
                bin2hex(random_bytes(32)),
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
        return '<input type="hidden" name="csrf_token" value="'
            . e(csrf_token())
            . '">';
    }
}

if (!function_exists('csrf_meta_tag')) {
    function csrf_meta_tag(): string
    {
        return '<meta name="csrf-token" content="'
            . e(csrf_token())
            . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): void
    {
        $request = app(Request::class);

        if (!$request->isPost()) {
            return;
        }

        $token = $request->post(
            'csrf_token',
        );

        $sessionToken = Session::get(
            'csrf_token',
        );

        $validToken =
            is_string($token)
            && $token !== ''
            && is_string($sessionToken)
            && $sessionToken !== ''
            && hash_equals(
                $sessionToken,
                $token,
            );

        if ($validToken) {
            return;
        }

        if ($request->isAjax()) {
            json([
                'success' => false,
                'message' => 'Session expirée, recharge la page.',
            ], 419);
        }

        abort(419);
    }
}
