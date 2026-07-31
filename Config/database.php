<?php

declare(strict_types=1);

return [

    // =========================================
    // CONNEXION
    // =========================================

    'host' => (string) env('DB_HOST', 'localhost'),
    'port' => max(1, env_int('DB_PORT', 3306)),
    'name' => (string) env('DB_NAME', ''),
    'user' => (string) env('DB_USER', ''),
    'pass' => (string) env('DB_PASS', ''),
    'charset' => (string) env('DB_CHARSET', 'utf8mb4'),

    // =========================================
    // PROFILING
    // =========================================

    'slow_query_threshold' => max(1, env_int('DB_SLOW_QUERY_THRESHOLD', 50))

];