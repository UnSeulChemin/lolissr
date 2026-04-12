<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('ROOT', dirname(__DIR__));

use App\Autoloader;
use App\Core\Main;

require_once ROOT . '/Autoloader.php';
Autoloader::register();

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

        if ($line === '' || str_starts_with($line, '#'))
        {
            continue;
        }

        if (!str_contains($line, '='))
        {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $_ENV[trim($name)] = trim($value);
    }
}

$app = new Main();
$app->start();