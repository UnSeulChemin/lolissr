<?php

declare(strict_types=1);

return [

    'app' => [

        'site_name' => 'LoliSSR',
        'base_path' => '/lolissr/',
        'pagination' => 8,
        'env' => $_ENV['APP_ENV'] ?? 'local',
        'debug' => isset($_ENV['APP_DEBUG'])
            ? filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOL)
            : true,

    ],

    'database' => [

        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? '',
        'user' => $_ENV['DB_USER'] ?? '',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',

    ],

];