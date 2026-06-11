<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Site'),

    'base_uri' => env('APP_BASE_URI', '/lolissr'),

    'env' => env('APP_ENV', 'local'),

    'debug' => env_bool('APP_DEBUG', false),

    'pagination' => max(
        1,
        env_int('APP_PAGINATION', 8)
    ),
];