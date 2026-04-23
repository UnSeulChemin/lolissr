<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Core\Application\App;
use App\Core\Config\Env;
use App\Core\Http\Response;

/*
|--------------------------------------------------------------------------
| dump()
|--------------------------------------------------------------------------
*/

if (!function_exists('dump'))
{
    /**
     * Affiche une ou plusieurs variables.
     */
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
    /**
     * Dump + stop.
     */
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
    /**
     * Retourne le base path de l'application.
     */
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
    /**
     * Retourne le chemin absolu de l'application.
     */
    function app_path(string $path = ''): string
    {
        return ROOT
            . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

/*
|--------------------------------------------------------------------------
| view_path()
|--------------------------------------------------------------------------
*/

if (!function_exists('view_path'))
{
    /**
     * Retourne le chemin vers les vues.
     */
    function view_path(string $view = ''): string
    {
        return app_path('App/Views')
            . ($view !== '' ? '/' . ltrim($view, '/') : '');
    }
}

/*
|--------------------------------------------------------------------------
| abort()
|--------------------------------------------------------------------------
*/

if (!function_exists('abort'))
{
    /**
     * Stoppe avec une erreur HTTP.
     */
    function abort(int $code = 404): void
    {
        $controller = new ErrorController();

        match ($code) {
            404 => $controller->renderNotFoundPage(),
            405 => $controller->renderMethodNotAllowedPage(),
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
    /**
     * Redirection globale.
     */
    function redirect(string $path = '', int $status = 302): void
    {
        if (preg_match('#^https?://#i', $path) === 1)
        {
            Response::redirect($path, $status);
            return;
        }

        $url = base_path() . ltrim($path, '/');

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
    /**
     * Réponse JSON.
     */
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
    /**
     * Rend une vue avec layout.
     *
     * @param array<string, mixed> $data
     */
    function view(
        string $view,
        array $data = [],
        ?string $title = null
    ): void
    {
        $basePath = base_path();
        $title ??= App::siteName();

        extract($data, EXTR_SKIP);

        $viewPath = view_path($view . '.php');
        $templatePath = view_path('layouts/base.php');

        if (!is_file($viewPath))
        {
            throw new RuntimeException(
                'Vue introuvable : ' . $view
            );
        }

        if (!is_file($templatePath))
        {
            throw new RuntimeException(
                'Template introuvable : layouts/base'
            );
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

if (!function_exists('env'))
{
    /**
     * Récupère une variable d'environnement.
     *
     * Exemple :
     * env('APP_DEBUG', false);
     */
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
        return Env::int($key, $default);
    }
}