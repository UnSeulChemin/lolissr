<?php

declare(strict_types=1);

namespace App\Support;

use Framework\Support\Str;

final class MangaThumbnailName
{
    private function __construct()
    {
    }

    // =========================================
    // THUMBNAIL
    // =========================================

    public static function generate(string $livre, int $numero): string
    {
        $slug = Str::slug($livre);

        if ($slug === '' || $numero <= 0)
        {
            return '';
        }

        return sprintf('%s-%02d', $slug, $numero);
    }
}