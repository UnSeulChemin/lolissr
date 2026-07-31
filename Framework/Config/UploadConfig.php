<?php

declare(strict_types=1);

namespace Framework\Config;

final class UploadConfig
{
    private const DEFAULT_MAX_SIZE = 5_242_880;
    private const DEFAULT_MAX_WIDTH = 10_000;
    private const DEFAULT_MAX_HEIGHT = 10_000;
    private const DEFAULT_MAX_PIXELS = 50_000_000;

    private function __construct()
    {
    }

    // =========================================
    // LIMITES
    // =========================================

    public static function maxSize(): int
    {
        return max(1, (int) config('upload.max_size', self::DEFAULT_MAX_SIZE));
    }

    public static function maxWidth(): int
    {
        return max(1, (int) config('upload.max_width', self::DEFAULT_MAX_WIDTH));
    }

    public static function maxHeight(): int
    {
        return max(1, (int) config('upload.max_height', self::DEFAULT_MAX_HEIGHT));
    }

    public static function maxPixels(): int
    {
        return max(1, (int) config('upload.max_pixels', self::DEFAULT_MAX_PIXELS));
    }

    // =========================================
    // FORMATS
    // =========================================

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return self::normalizeList(config('upload.allowed_extensions', []));
    }

    /**
     * @return list<string>
     */
    public static function allowedMimeTypes(): array
    {
        return self::normalizeList(config('upload.allowed_mime_types', []));
    }

    // =========================================
    // NORMALISATION
    // =========================================

    /**
     * @return list<string>
     */
    private static function normalizeList(mixed $values): array
    {
        if (! is_array($values))
        {
            return [];
        }

        $values = array_map(
            static fn (mixed $value): string => strtolower(trim((string) $value)),
            $values
        );

        $values = array_filter($values, static fn (string $value): bool => $value !== '');

        return array_values(array_unique($values));
    }
}