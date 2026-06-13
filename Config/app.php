<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Site'),
    'version' => env('APP_VERSION', '1.0.0'),
    'base_uri' => env('APP_BASE_URI', '/lolissr'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env_bool('APP_DEBUG', false),
    'pagination' => max(1, env_int('APP_PAGINATION', 8)),
];