<?php

declare(strict_types=1);

namespace Framework\Config;

final class UploadConfig
{
    private static ?string $mangaThumbnailDirectory = null;

    public static function maxSize(): int
    {
        return max(
            1,
            (int) config(
                'upload.max_size',
                5242880,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return self::normalizedList(
            config(
                'upload.allowed_extensions',
                [],
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        return self::normalizedList(
            config(
                'upload.allowed_mime_types',
                [],
            ),
        );
    }

    public static function mangaThumbnailDirectory(): string
    {
        return self::$mangaThumbnailDirectory
            ??= rtrim(
                base_path(
                    'public/images/mangas/thumbnail',
                ),
                '/\\',
            )
            . DIRECTORY_SEPARATOR;
    }

    /**
     * @param mixed $values
     * @return list<string>
     */
    private static function normalizedList(
        mixed $values,
    ): array {

        if (! is_array($values))
        {
            return [];
        }

        $values =
            array_map(
                static fn (
                    mixed $value,
                ): string => strtolower(
                    trim(
                        (string) $value,
                    ),
                ),
                $values,
            );

        $values =
            array_filter(
                $values,
                static fn (
                    string $value,
                ): bool => $value !== '',
            );

        return array_values(
            array_unique(
                $values,
            ),
        );
    }
}