<?php

declare(strict_types=1);

use App\Core\Functions;

return [

    'max_size' => (int) Functions::env('UPLOAD_MAX_SIZE', 5242880),

    'allowed_extensions' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) Functions::env('UPLOAD_ALLOWED_EXT', 'jpg,jpeg,png,webp'))
    ))),

    'allowed_mime' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) Functions::env('UPLOAD_ALLOWED_MIME', 'image/jpeg,image/png,image/webp'))
    ))),

];