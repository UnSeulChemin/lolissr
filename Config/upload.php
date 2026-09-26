<?php

declare(strict_types=1);

// Config is loaded once per request; consumers receive normalized lists.
$normalizeList = static function (string $value): array
{
    return array_values(array_unique(array_filter(
        array_map(static fn (string $item): string => strtolower(trim($item)), explode(',', $value)),
        static fn (string $item): bool => $item !== ''
    )));
};

return [

    // =========================================
    // LIMITES
    // =========================================

    'max_size' => max(1, env_int('UPLOAD_MAX_SIZE', 5_242_880)),

    // =========================================
    // DIMENSIONS
    // =========================================

    'max_width' => max(1, env_int('UPLOAD_MAX_WIDTH', 10_000)),
    'max_height' => max(1, env_int('UPLOAD_MAX_HEIGHT', 10_000)),
    'max_pixels' => max(1, env_int('UPLOAD_MAX_PIXELS', 50_000_000)),

    // =========================================
    // FORMATS
    // =========================================

    'allowed_extensions' => $normalizeList(
        (string) env('UPLOAD_ALLOWED_EXT', 'jpg,jpeg,png,webp')
    ),

    'allowed_mime_types' => $normalizeList(
        (string) env(
            'UPLOAD_ALLOWED_MIME',
            'image/jpeg,image/png,image/webp'
        )
    )

];