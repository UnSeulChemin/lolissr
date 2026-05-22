<?php

declare(strict_types=1);

namespace Framework\Support;

final class MangaNoteNormalizer
{
    public static function normalize(
        mixed $value,
    ): ?int {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return self::normalizeInteger(
                $value,
            );
        }

        $value = trim(
            (string) $value,
        );

        if (
            $value === ''
            || !ctype_digit($value)
        ) {
            return null;
        }

        return self::normalizeInteger(
            (int) $value,
        );
    }

    private static function normalizeInteger(
        int $note,
    ): ?int {
        return ($note >= 1 && $note <= 5)
            ? $note
            : null;
    }
}