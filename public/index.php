<?php

declare(strict_types=1);

session_start();

define('ROOT', dirname(__DIR__));

require_once ROOT . '/Autoloader.php';

\App\Autoloader::register();

/*
|--------------------------------------------------------------------------
| Chargement du fichier .env
|--------------------------------------------------------------------------
*/
$envFile = ROOT . '/.env';

if (is_file($envFile))
{
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line)
    {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '='))
        {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv($name . '=' . $value);
    }
}

/*
|--------------------------------------------------------------------------
| Gestion du debug
|--------------------------------------------------------------------------
*/
$debug = \App\Core\Functions::appDebug();

error_reporting($debug ? E_ALL : 0);
ini_set('display_errors', $debug ? '1' : '0');

/*
|--------------------------------------------------------------------------
| Gestionnaire global d'erreurs
|--------------------------------------------------------------------------
*/
\App\Core\ErrorHandler::register();

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
$router->dispatch(
    $_SERVER['REQUEST_URI'] ?? '/',
    $_SERVER['REQUEST_METHOD'] ?? 'GET'
);