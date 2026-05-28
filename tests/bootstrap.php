<?php

declare(strict_types=1);

if (!defined('ROOT')) {
    define(
        'ROOT',
        dirname(__DIR__),
    );
}

/*
|--------------------------------------------------------------------------
| AUTOLOAD
|--------------------------------------------------------------------------
*/

require_once ROOT . '/vendor/autoload.php';

require_once ROOT . '/Framework/Support/helpers.php';

/*
|--------------------------------------------------------------------------
| ENVIRONMENT
|--------------------------------------------------------------------------
|
| Charge le .env puis force le runtime testing.
| Aucun test mutateur ne doit être actif par défaut.
|
*/

$envFile =
    base_path('.env');

if (is_file($envFile)) {

    $lines = file(
        $envFile,
        FILE_IGNORE_NEW_LINES
        | FILE_SKIP_EMPTY_LINES,
    ) ?: [];

    foreach ($lines as $line) {

        $line =
            trim($line);

        if (
            $line === ''
            || str_starts_with($line, '#')
            || !str_contains($line, '=')
        ) {
            continue;
        }

        [
            $name,
            $value,
        ] = explode(
            '=',
            $line,
            2,
        );

        $name =
            trim($name);

        $value =
            trim($value);

        $_ENV[$name] =
            $value;

        $_SERVER[$name] =
            $value;

        putenv(
            "{$name}={$value}",
        );
    }
}

/*
|--------------------------------------------------------------------------
| FORCE TEST ENV
|--------------------------------------------------------------------------
*/

$_ENV['APP_ENV'] =
    'testing';

$_SERVER['APP_ENV'] =
    'testing';

putenv(
    'APP_ENV=testing',
);

/*
|--------------------------------------------------------------------------
| SAFE TEST MODE
|--------------------------------------------------------------------------
|
| Tous les tests sont non-mutateurs par défaut.
| Aucune écriture DB/fichier réelle.
|
*/

$testEnv = [

    'TESTS_ENABLED' =>
        'true',

    'TEST_UPLOAD_MODE' =>
        'true',

    'TEST_UPLOAD_REAL' =>
        'false',

    'TEST_POST_AJOUTER' =>
        'false',

    'TEST_POST_UPDATE' =>
        'false',

    'TEST_AJAX_UPDATE' =>
        'false',

    'TEST_UPLOAD_DUPLICATE_SLUG_NUMERO' =>
        'false',

    'TEST_UPLOAD_INVALID_IMAGE' =>
        'false',

    'TEST_UPLOAD_MAX_SIZE' =>
        'false',
];

foreach (
    $testEnv
    as $key => $value
) {

    $_ENV[$key] =
        $value;

    $_SERVER[$key] =
        $value;

    putenv(
        "{$key}={$value}",
    );
}

/*
|--------------------------------------------------------------------------
| RUNTIME SAFETY
|--------------------------------------------------------------------------
|
| Vérification finale :
| le runtime DOIT être en lecture seule.
|
*/

if (
    ($_ENV['APP_ENV'] ?? '')
    !== 'testing'
) {

    throw new RuntimeException(
        'Le runtime de test doit être en environnement testing.',
    );
}