<?php

declare(strict_types=1);

use App\Core\Config\Env;

return [

    'name' => Env::get('APP_NAME', 'Site'),
    'base_path' => Env::get('APP_BASE_PATH', '/'),
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => Env::bool('APP_DEBUG', false),
    'pagination' => max(1, (int) Env::get('APP_PAGINATION', 8)),

];