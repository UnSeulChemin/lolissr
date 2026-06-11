<?php

declare(strict_types=1);

namespace Framework\Support;

final class MangaNoteNormalizer
{
    public static function normalize(
        mixed $value,
    ): ?int {

        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        if (
            is_string($value)
        ) {
            $value =
                trim($value);

            if (! ctype_digit($value))
            {
                return null;
            }

            $value =
                (int) $value;
        }

        if (! is_int($value))
        {
            return null;
        }

        return (
            $value >= 1
            && $value <= 5
        )
            ? $value
            : null;
    }
}