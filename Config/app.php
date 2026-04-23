<?php

declare(strict_types=1);

return [

    'name' => env('APP_NAME', 'Site'),
    'base_path' => env('APP_BASE_PATH', '/'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env_bool('APP_DEBUG', false),
    'pagination' => max(1, (int) env('APP_PAGINATION', 8)),

];