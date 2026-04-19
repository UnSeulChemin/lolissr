<?php

declare(strict_types=1);

namespace App\Core;

final class Str
{
    /**
     * Normalise une chaîne en slug.
     */
    public static function slug(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $value) ?? '';
        $value = preg_replace('/[\s-]+/u', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value;
    }

    /**
     * Trim une chaîne nullable.
     * Retourne null si la valeur est vide.
     */
    public static function nullableTrim(?string $value): ?string
    {
        if ($value === null)
        {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Génère un nom de thumbnail propre.
     */
    public static function thumbnailName(string $livre, int $numero): string
    {
        $thumbnail = self::slug($livre);

        if ($thumbnail === '' || $numero <= 0)
        {
            return '';
        }

        return $thumbnail . '-' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie si une chaîne est vide après trim.
     */
    public static function isBlank(?string $value): bool
    {
        return self::nullableTrim($value) === null;
    }
}