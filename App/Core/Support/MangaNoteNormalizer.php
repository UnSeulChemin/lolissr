<?php

declare(strict_types=1);

namespace App\Core\Support;

final class MangaNoteNormalizer
{
    public static function normalize(
        mixed $value
    ): ?int {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if (
            $value === ''
            || !ctype_digit($value)
        ) {
            return null;
        }

        $note = (int) $value;

        if ($note < 1 || $note > 5) {
            return null;
        }

        return $note;
    }
}
