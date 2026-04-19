<?php

declare(strict_types=1);

use App\Core\Functions;

return [

    'name' => (string) Functions::env('APP_NAME', 'LoliSSR'),
    'base_path' => (string) Functions::env('APP_BASE_PATH', '/'),
    'env' => (string) Functions::env('APP_ENV', 'production'),
    'debug' => (bool) Functions::env('APP_DEBUG', false),
    'pagination' => (int) Functions::env('APP_PAGINATION', 8),

];