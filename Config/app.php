<?php

declare(strict_types=1);

return [

    // =========================================
    // APPLICATION
    // =========================================

    // Required values are checked by EnvironmentValidator before loading configuration.
    'name' => (string) env('APP_NAME'),
    'version' => (string) env('APP_VERSION', '1.0.0'),
    'base_uri' => (string) env('APP_BASE_URI'),
    'env' => (string) env('APP_ENV'),
    'timezone' => (string) env('APP_TIMEZONE'),
    'trust_proxy' => env_bool('TRUST_PROXY', false),

    // =========================================
    // OPTIONS
    // =========================================

    'debug' => env_bool('APP_DEBUG', false),
    'profiler' => env_bool('PROFILER_ENABLED', false),
    'pagination' => max(1, env_int('APP_PAGINATION', 8))

];
