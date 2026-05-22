<?php

declare(strict_types=1);

namespace Framework\Config;

final class UploadConfig
{
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
     * @return string[]
     */
    public static function allowedExtensions(): array
    {
        $extensions = config(
            'upload.allowed_extensions',
            [],
        );

        if (!is_array($extensions)) {
            return [];
        }

        return array_values(
            array_unique($extensions),
        );
    }

    /**
     * @return string[]
     */
    public static function allowedMimeTypes(): array
    {
        $mimeTypes = config(
            'upload.allowed_mime_types',
            [],
        );

        if (!is_array($mimeTypes)) {
            return [];
        }

        return array_values(
            array_unique($mimeTypes),
        );
    }

    public static function mangaThumbnailDirectory(): string
    {
        return rtrim(
            base_path(
                'public/images/mangas/thumbnail',
            ),
            '/\\',
        ) . DIRECTORY_SEPARATOR;
    }
}