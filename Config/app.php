<?php

declare(strict_types=1);

return [

    'name' => env('APP_NAME', 'Site'),

    /*
    |--------------------------------------------------------------------------
    | Base URI
    |--------------------------------------------------------------------------
    |
    | Useful if the application is hosted in a subdirectory.
    | Example:
    | - https://example.com       => /
    | - https://example.com/app   => /app
    |
    */

    'base_uri' => env('APP_BASE_URI', '/'),

    'env' => env('APP_ENV', 'local'),

    'debug' => env_bool('APP_DEBUG', false),

    'pagination' => max(
        1,
        env_int('APP_PAGINATION', 8),
    ),

];