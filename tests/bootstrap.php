<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/Autoloader.php';

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

        if (
            $line === ''
            || str_starts_with($line, '#')
            || !str_contains($line, '=')
        ) {
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
| Mode test
|--------------------------------------------------------------------------
*/

$_ENV['APP_ENV'] = 'test';
$_SERVER['APP_ENV'] = 'test';
putenv('APP_ENV=test');

$_ENV['TEST_UPLOAD_MODE'] = 'true';
$_SERVER['TEST_UPLOAD_MODE'] = 'true';
putenv('TEST_UPLOAD_MODE=true');