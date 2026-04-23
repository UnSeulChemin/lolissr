<?php

declare(strict_types=1);

use App\Controllers\ErrorController;
use App\Core\Application\App;
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
        if (preg_match('#^https?://#i', $path))
        {
            Response::redirect($path, $status);
            return;
        }

        $basePath = App::basePath();

        $url = $basePath . ltrim($path, '/');

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
     */
    function view(
        string $view,
        array $data = [],
        ?string $title = null
    ): void
    {
        $basePath = App::basePath();
        $title ??= App::siteName();

        extract($data, EXTR_SKIP);

        $viewPath = ROOT . '/App/Views/' . $view . '.php';
        $templatePath = ROOT . '/App/Views/layouts/base.php';

        if (!is_file($viewPath))
        {
            throw new RuntimeException(
                "Vue introuvable : {$view}"
            );
        }

        if (!is_file($templatePath))
        {
            throw new RuntimeException(
                "Template introuvable"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Render view
        |--------------------------------------------------------------------------
        */

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        /*
        |--------------------------------------------------------------------------
        | Render layout
        |--------------------------------------------------------------------------
        */

        ob_start();
        require $templatePath;
        $html = ob_get_clean();

        Response::html($html);
    }
}