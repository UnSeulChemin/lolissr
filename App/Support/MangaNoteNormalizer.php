<?php

declare(strict_types=1);

namespace App\Support;

final class MangaNoteNormalizer
{
    private const MIN_NOTE = 1;
    private const MAX_NOTE = 5;

    private function __construct()
    {
    }

    // =========================================
    // NOTE
    // =========================================

    public static function normalize(mixed $value): ?int
    {
        if (is_string($value))
        {
            $value = trim($value);

            if ($value === '' || ! ctype_digit($value))
            {
                return null;
            }

            $value = (int) $value;
        }

        if (! is_int($value))
        {
            return null;
        }

        return $value >= self::MIN_NOTE && $value <= self::MAX_NOTE
            ? $value
            : null;
    }
}