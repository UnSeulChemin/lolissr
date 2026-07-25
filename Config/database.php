<?php

declare(strict_types=1);

return [
    'host' => env('DB_HOST', 'localhost'),
    'port' => env_int('DB_PORT', 3306),
    'name' => env('DB_NAME', ''),
    'user' => env('DB_USER', ''),
    'pass' => env('DB_PASS', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'slow_query_threshold' => max(1, env_int('DB_SLOW_QUERY_THRESHOLD', 50)),
];