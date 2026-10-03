<?php

declare(strict_types=1);

namespace Framework\Support;

final class Str
{
    private function __construct()
    {
    }

    // =================================================
    // CHAÎNES
    // =================================================

    public static function slug(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        if ($value === '')
        {
            return '';
        }

        $value = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $value) ?? '';

        $value = preg_replace('/[\s-]+/u', '-', $value) ?? '';

        return trim($value, '-');
    }

    public static function nullableTrim(?string $value): ?string
    {
        if ($value === null)
        {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    public static function asciiSlug(string $value): string
    {
        $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII', $value);
        $slug = mb_strtolower($ascii !== false ? $ascii : $value, 'UTF-8');

        return trim(preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '', '-');
    }
}
