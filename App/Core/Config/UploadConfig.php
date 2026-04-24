<?php

declare(strict_types=1);

namespace App\Core\Config;

final class UploadConfig
{
    public static function maxSize(): int
    {
        return max(1, (int) config('upload.max_size', 5242880));
    }

    /**
     * @return string[]
     */
    public static function allowedExtensions(): array
    {
        $extensions = config('upload.allowed_extensions', []);

        if (!is_array($extensions))
        {
            return [];
        }

        $extensions = array_map(
            static fn (mixed $extension): string => strtolower(trim((string) $extension)),
            $extensions
        );

        $extensions = array_filter(
            $extensions,
            static fn (string $extension): bool => $extension !== ''
        );

        return array_values(array_unique($extensions));
    }

    /**
     * @return string[]
     */
    public static function allowedMimeTypes(): array
    {
        $mimeTypes = config('upload.allowed_mime', []);

        if (!is_array($mimeTypes))
        {
            return [];
        }

        $mimeTypes = array_map(
            static fn (mixed $mimeType): string => strtolower(trim((string) $mimeType)),
            $mimeTypes
        );

        $mimeTypes = array_filter(
            $mimeTypes,
            static fn (string $mimeType): bool => $mimeType !== ''
        );

        return array_values(array_unique($mimeTypes));
    }

    public static function mangaThumbnailDirectory(): string
    {
        return rtrim(
            app_path('public/images/mangas/thumbnail'),
            '/\\'
        ) . DIRECTORY_SEPARATOR;
    }
}