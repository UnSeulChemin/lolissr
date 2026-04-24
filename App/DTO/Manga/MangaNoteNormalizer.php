<?php

declare(strict_types=1);

namespace App\DTO\Manga;

final class MangaNoteNormalizer
{
    public static function normalize(mixed $value): ?int
    {
        if ($value === null)
        {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '')
        {
            return null;
        }

        $value = (int) $value;

        if ($value < 1 || $value > 5)
        {
            return null;
        }

        return $value;
    }
}