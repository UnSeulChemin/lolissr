<?php

declare(strict_types=1);

namespace Framework\Support;

final class Str
{
    public static function slug(
        string $value,
    ): string {

        $value =
            mb_strtolower(
                trim($value),
                'UTF-8',
            );

        $value =
            preg_replace(
                '/[^\p{L}\p{N}\s-]/u',
                '',
                $value,
            ) ?? '';

        $value =
            preg_replace(
                '/[\s-]+/u',
                '-',
                $value,
            ) ?? '';

        return trim(
            $value,
            '-',
        );
    }

    public static function nullableTrim(
        ?string $value,
    ): ?string {

        if ($value === null)
        {
            return null;
        }

        $value =
            trim($value);

        return $value !== ''
            ? $value
            : null;
    }

    public static function isBlank(
        ?string $value,
    ): bool {

        return self::nullableTrim(
            $value,
        ) === null;
    }

    public static function thumbnailName(
        string $livre,
        int $numero,
    ): string {

        $thumbnail =
            self::slug(
                $livre,
            );

        if (
            $thumbnail === ''
            || $numero <= 0
        ) {
            return '';
        }

        return sprintf(
            '%s-%02d',
            $thumbnail,
            $numero,
        );
    }
}