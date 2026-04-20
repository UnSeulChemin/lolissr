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
        )
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
| Mode test forcé
|--------------------------------------------------------------------------
|
| Ce fichier force un contexte de test runtime.
| Il ne doit pas autoriser d'écriture mutatrice par défaut.
|
*/

$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');

/*
|--------------------------------------------------------------------------
| Tests safe par défaut
|--------------------------------------------------------------------------
*/

$_ENV['TESTS_ENABLED'] = 'true';
$_SERVER['TESTS_ENABLED'] = 'true';
putenv('TESTS_ENABLED=true');

$_ENV['TEST_UPLOAD_MODE'] = 'true';
$_SERVER['TEST_UPLOAD_MODE'] = 'true';
putenv('TEST_UPLOAD_MODE=true');

$_ENV['TEST_UPLOAD_REAL'] = 'false';
$_SERVER['TEST_UPLOAD_REAL'] = 'false';
putenv('TEST_UPLOAD_REAL=false');

$_ENV['TEST_POST_AJOUTER'] = 'false';
$_SERVER['TEST_POST_AJOUTER'] = 'false';
putenv('TEST_POST_AJOUTER=false');

$_ENV['TEST_POST_UPDATE'] = 'false';
$_SERVER['TEST_POST_UPDATE'] = 'false';
putenv('TEST_POST_UPDATE=false');

$_ENV['TEST_AJAX_UPDATE'] = 'false';
$_SERVER['TEST_AJAX_UPDATE'] = 'false';
putenv('TEST_AJAX_UPDATE=false');