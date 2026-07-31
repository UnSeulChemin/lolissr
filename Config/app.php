<?php

declare(strict_types=1);

return [

    // =========================================
    // APPLICATION
    // =========================================

    'name' => (string) env('APP_NAME', 'Site'),
    'version' => (string) env('APP_VERSION', '1.0.0'),
    'base_uri' => (string) env('APP_BASE_URI', '/lolissr'),
    'env' => (string) env('APP_ENV', 'local'),
    'timezone' => (string) env('APP_TIMEZONE', 'Europe/Paris'),

    // =========================================
    // OPTIONS
    // =========================================

    'debug' => env_bool('APP_DEBUG', false),
    'profiler' => env_bool('PROFILER_ENABLED', false),
    'pagination' => max(1, env_int('APP_PAGINATION', 8))

];