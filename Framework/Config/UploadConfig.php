<?php

declare(strict_types=1);

namespace Framework\Config;

use InvalidArgumentException;

final class UploadConfig
{
    /**
     * @var array<string, string>
     */
    private static array $thumbnailDirectories = [];

    private function __construct()
    {
    }

    // =========================================
    // CONFIGURATION
    // =========================================

    public static function maxSize(): int
    {
        return max(1, (int) config('upload.max_size', 5242880));
    }

    public static function maxWidth(): int
    {
        return max(1, (int) config('upload.max_width', 10000));
    }

    public static function maxHeight(): int
    {
        return max(1, (int) config('upload.max_height', 10000));
    }

    public static function maxPixels(): int
    {
        return max(1, (int) config('upload.max_pixels', 50000000));
    }

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return self::normalizedList(config('upload.allowed_extensions', []));
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        return self::normalizedList(config('upload.allowed_mime_types', []));
    }

    public static function thumbnailDirectory(string $folder): string
    {
        $folder = self::normalizeFolder($folder);

        return self::$thumbnailDirectories[$folder]
            ??= rtrim(
                base_path("public/images/{$folder}/thumbnail"),
                '/\\'
            ) . DIRECTORY_SEPARATOR;
    }

    // =========================================
    // NORMALISATION
    // =========================================

    /**
     * @return list<string>
     */
    private static function normalizedList(mixed $values): array
    {
        if (! is_array($values))
        {
            return [];
        }

        $values = array_map(
            static fn (mixed $value): string => strtolower(trim((string) $value)),
            $values
        );

        $values = array_filter(
            $values,
            static fn (string $value): bool => $value !== ''
        );

        return array_values(array_unique($values));
    }

    private static function normalizeFolder(string $folder): string
    {
        $folder = strtolower(trim($folder));

        if ($folder === '' || preg_match('/^[a-z0-9_-]+$/', $folder) !== 1)
        {
            throw new InvalidArgumentException(
                "Invalid upload folder: {$folder}"
            );
        }

        return $folder;
    }
}