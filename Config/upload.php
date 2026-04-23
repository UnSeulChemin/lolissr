<?php

declare(strict_types=1);

return [

    'max_size' => max(1, env_int('UPLOAD_MAX_SIZE', 5242880)),

    'allowed_extensions' => array_values(array_filter(array_map(
        static fn (string $extension): string => strtolower(trim($extension)),
        explode(',', (string) env('UPLOAD_ALLOWED_EXT', 'jpg,jpeg,png,webp'))
    ))),

    'allowed_mime' => array_values(array_filter(array_map(
        static fn (string $mime): string => strtolower(trim($mime)),
        explode(',', (string) env('UPLOAD_ALLOWED_MIME', 'image/jpeg,image/png,image/webp'))
    ))),

];