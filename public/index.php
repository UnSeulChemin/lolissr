<?php

declare(strict_types=1);

session_start();

define('ROOT', dirname(__DIR__));

require_once ROOT . '/Autoloader.php';

\App\Autoloader::register();
\App\Core\ErrorHandler::register();

/*
|--------------------------------------------------------------------------
| Chargement du fichier .env
|--------------------------------------------------------------------------
*/
$envFile = ROOT . '/.env';

if (is_file($envFile))
{
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line)
    {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '='))
        {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $_ENV[trim($name)] = trim($value);
    }
}

/*
|--------------------------------------------------------------------------
| Gestion du debug
|--------------------------------------------------------------------------
*/
$debug = \App\Core\Functions::appDebug();

if ($debug)
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
else
{
    error_reporting(0);
    ini_set('display_errors', '0');
}

/*
|--------------------------------------------------------------------------
| Gestion globale des erreurs PHP
|--------------------------------------------------------------------------
*/
set_error_handler(function (
    int $severity,
    string $message,
    string $file,
    int $line
): bool {
    \App\Core\Logger::error(
        "PHP Error [{$severity}] {$message} dans {$file} à la ligne {$line}"
    );

    if (\App\Core\Functions::appDebug())
    {
        echo "PHP Error [{$severity}] {$message} dans {$file} à la ligne {$line}";
    }

    return true;
});

/*
|--------------------------------------------------------------------------
| Gestion globale des exceptions
|--------------------------------------------------------------------------
*/
set_exception_handler(function (\Throwable $exception): void {
    \App\Core\Logger::error(
        'Uncaught Exception: '
        . $exception->getMessage()
        . ' dans '
        . $exception->getFile()
        . ' à la ligne '
        . $exception->getLine()
    );

    http_response_code(500);

    if (\App\Core\Functions::appDebug())
    {
        exit(
            'Exception : '
            . $exception->getMessage()
            . ' dans '
            . $exception->getFile()
            . ' à la ligne '
            . $exception->getLine()
        );
    }

    $controller = new \App\Controllers\ErrorController();
    $controller->serverError('Une erreur interne est survenue.');
});

/*
|--------------------------------------------------------------------------
| Gestion des erreurs fatales
|--------------------------------------------------------------------------
*/
register_shutdown_function(function (): void {
    $error = error_get_last();

    if ($error === null)
    {
        return;
    }

    \App\Core\Logger::error(
        "Fatal Error [{$error['type']}] {$error['message']} dans {$error['file']} à la ligne {$error['line']}"
    );

    http_response_code(500);

    if (\App\Core\Functions::appDebug())
    {
        echo 'Fatal Error : '
            . $error['message']
            . ' dans '
            . $error['file']
            . ' à la ligne '
            . $error['line'];

        return;
    }

    $controller = new \App\Controllers\ErrorController();
    $controller->serverError('Une erreur fatale est survenue.');
});

/*
|--------------------------------------------------------------------------
| Lancement de l'application
|--------------------------------------------------------------------------
*/
$router = new \App\Core\Router();

/*
|--------------------------------------------------------------------------
| Chargement des routes
|--------------------------------------------------------------------------
*/
$routes = require ROOT . '/Config/routes.php';
$routes($router);

/*
|--------------------------------------------------------------------------
| Dispatch
|--------------------------------------------------------------------------
*/
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);