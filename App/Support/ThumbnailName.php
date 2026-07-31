<?php

declare(strict_types=1);

namespace App\Support;

use Framework\Support\Str;

final class ThumbnailName
{
    private function __construct()
    {
    }

    // =========================================
    // GÉNÉRATION
    // =========================================

    public static function generate(string $name, int $numero): string
    {
        $slug = Str::slug($name);

        if ($slug === '' || $numero <= 0)
        {
            return '';
        }

        return sprintf('%s-%02d', $slug, $numero);
    }
}