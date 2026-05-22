<?php

declare(strict_types=1);

return [

    'host' => env('DB_HOST', 'localhost'),

    'port' => env_int('DB_PORT', 3306),

    'name' => env('DB_NAME', ''),

    'user' => env('DB_USER', ''),

    'pass' => env('DB_PASS', ''),

    'charset' => env('DB_CHARSET', 'utf8mb4'),

];