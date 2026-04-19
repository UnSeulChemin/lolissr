<?php

declare(strict_types=1);

use App\Core\Env;

return [

    'host' => Env::get('DB_HOST', 'localhost'),
    'name' => Env::get('DB_NAME', ''),
    'user' => Env::get('DB_USER', ''),
    'pass' => Env::get('DB_PASS', ''),
    'charset' => Env::get('DB_CHARSET', 'utf8mb4'),

];