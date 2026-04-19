<?php

declare(strict_types=1);

use App\Core\Functions;

return [

    'host' => (string) Functions::env('DB_HOST', 'localhost'),
    'name' => (string) Functions::env('DB_NAME', ''),
    'user' => (string) Functions::env('DB_USER', ''),
    'pass' => (string) Functions::env('DB_PASS', ''),
    'charset' => (string) Functions::env('DB_CHARSET', 'utf8mb4'),

];