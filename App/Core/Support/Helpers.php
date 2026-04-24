<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Core\Application\App;
use App\Core\Config\Config;
use App\Core\Config\Env;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Support\Session;

/*
|--------------------------------------------------------------------------
| dump()
|--------------------------------------------------------------------------
*/

if (!function_exists('dump'))
{
    function dump(mixed ...$vars): void
    {
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

        foreach ($vars as $var)
        {
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

if (!function_exists('dd'))
{
    function dd(mixed ...$vars): void
    {
        dump(...$vars);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| base_path()
|--------------------------------------------------------------------------
*/

if (!function_exists('base_path'))
{
    function base_path(): string
    {
        return App::basePath();
    }
}

/*
|--------------------------------------------------------------------------
| app_path()
|--------------------------------------------------------------------------
*/

if (!function_exists('app_path'))
{
    function app_path(string $path = ''): string
    {
        return rtrim(ROOT, DIRECTORY_SEPARATOR)
            . ($path !== ''
                ? DIRECTORY_SEPARATOR . ltrim($path, '/\\')
                : '');
    }
}

/*
|--------------------------------------------------------------------------
| view_path()
|--------------------------------------------------------------------------
*/

if (!function_exists('view_path'))
{
    function view_path(string $view = ''): string
    {
        return app_path('App/Views')
            . ($view !== ''
                ? DIRECTORY_SEPARATOR . ltrim($view, '/\\')
                : '');
    }
}

/*
|--------------------------------------------------------------------------
| abort()
|--------------------------------------------------------------------------
*/

if (!function_exists('abort'))
{
    function abort(int $code = 404): void
    {
        $controller = new ErrorController();

        match ($code) {
            404 => $controller->renderNotFoundPage(),
            405 => $controller->renderMethodNotAllowedPage(),
            419 => $controller->renderCsrfExpiredPage(),
            500 => $controller->renderServerErrorPage(),
            default => $controller->renderServerErrorPage(),
        };

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| redirect()
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect'))
{
    function redirect(string $path = '', int $status = 302): void
    {
        if (preg_match('#^https?://#i', $path) === 1)
        {
            Response::redirect($path, $status);
            return;
        }

        $url = rtrim(base_path(), '/') . '/' . ltrim($path, '/');

        Response::redirect($url, $status);
    }
}

/*
|--------------------------------------------------------------------------
| json()
|--------------------------------------------------------------------------
*/

if (!function_exists('json'))
{
    function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }
}

/*
|--------------------------------------------------------------------------
| view()
|--------------------------------------------------------------------------
*/

if (!function_exists('view'))
{
    function view(
        string $view,
        array $data = [],
        ?string $title = null
    ): void {
        $basePath = base_path();
        $title ??= App::siteName();

        extract($data, EXTR_SKIP);

        $viewPath = view_path($view . '.php');
        $templatePath = view_path('layouts/base.php');

        if (!is_file($viewPath))
        {
            throw new RuntimeException('Vue introuvable : ' . $view);
        }

        if (!is_file($templatePath))
        {
            throw new RuntimeException('Template introuvable : layouts/base');
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean() ?: '';

        ob_start();
        require $templatePath;
        $html = ob_get_clean() ?: '';

        Response::html($html);
    }
}

/*
|--------------------------------------------------------------------------
| env()
|--------------------------------------------------------------------------
*/

if (!function_exists('env'))
{
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('env_bool'))
{
    function env_bool(string $key, bool $default = false): bool
    {
        return Env::bool($key, $default);
    }
}

if (!function_exists('env_int'))
{
    function env_int(string $key, int $default = 0): int
    {
        return (int) Env::get($key, $default);
    }
}

/*
|--------------------------------------------------------------------------
| config()
|--------------------------------------------------------------------------
*/

if (!function_exists('config'))
{
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

/*
|--------------------------------------------------------------------------
| is_ajax()
|--------------------------------------------------------------------------
*/

if (!function_exists('is_ajax'))
{
    function is_ajax(): bool
    {
        return Request::isAjax();
    }
}

if (!function_exists('csrf_token'))
{
    function csrf_token(): string
    {
        if (!Session::has('csrf_token'))
        {
            Session::set(
                'csrf_token',
                bin2hex(random_bytes(32))
            );
        }

        return Session::get('csrf_token');
    }
}

if (!function_exists('csrf_field'))
{
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }
}

if (!function_exists('csrf_verify'))
{
    function csrf_verify(): void
    {
        if (!Request::isPost())
        {
            return;
        }

        $token = Request::post('csrf_token');
        $sessionToken = Session::get('csrf_token');

        $validToken =
            is_string($token)
            && $token !== ''
            && is_string($sessionToken)
            && $sessionToken !== ''
            && hash_equals($sessionToken, $token);

        if ($validToken)
        {
            return;
        }

        if (Request::isAjax())
        {
            json([
                'success' => false,
                'message' => 'Session expirée, recharge la page.'
            ], 419);
        }

        abort(419);
    }
}

if (!function_exists('csrf_meta_tag'))
{
    function csrf_meta_tag(): string
    {
        return '<meta name="csrf-token" content="'
            . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }
}