<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configuration principale
|--------------------------------------------------------------------------
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    'app' => [

        /* Nom du site */
        'site_name' => 'LoliSSR',

        /* Chemin de base */
        'base_path' => '/lolissr/',

        /* Pagination */
        'pagination' => 8,

        /* Environnement (local | production) */
        'env' => $_ENV['APP_ENV'] ?? 'local',

        /* Mode debug */
        'debug' => isset($_ENV['APP_DEBUG'])
            ? filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOL)
            : true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Base de données
    |--------------------------------------------------------------------------
    */

    'database' => [

        /* Hôte */
        'host' => $_ENV['DB_HOST'] ?? 'localhost',

        /* Nom base */
        'name' => $_ENV['DB_NAME'] ?? '',

        /* Utilisateur */
        'user' => $_ENV['DB_USER'] ?? '',

        /* Mot de passe */
        'pass' => $_ENV['DB_PASS'] ?? '',

        /* Charset */
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',

    ],

];