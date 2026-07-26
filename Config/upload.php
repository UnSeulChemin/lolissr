<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Upload limits
    |--------------------------------------------------------------------------
    */

    'max_size' => max(1, env_int('UPLOAD_MAX_SIZE', 5242880)),

    /*
    |--------------------------------------------------------------------------
    | Image dimensions
    |--------------------------------------------------------------------------
    */

    'max_width' => max(1, env_int('UPLOAD_MAX_WIDTH', 10000)),
    'max_height' => max(1, env_int('UPLOAD_MAX_HEIGHT', 10000)),
    'max_pixels' => max(1, env_int('UPLOAD_MAX_PIXELS', 50000000)),

    /*
    |--------------------------------------------------------------------------
    | Allowed file extensions
    |--------------------------------------------------------------------------
    */

    'allowed_extensions' => explode(',', (string) env('UPLOAD_ALLOWED_EXT', 'jpg,jpeg,png,webp')),

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME types
    |--------------------------------------------------------------------------
    */

    'allowed_mime_types' => explode(',', (string) env('UPLOAD_ALLOWED_MIME', 'image/jpeg,image/png,image/webp')),

];