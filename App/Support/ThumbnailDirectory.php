<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class ThumbnailDirectory
{
    /**
     * @var array<string, string>
     */
    private static array $directories = [];

    private function __construct()
    {
    }

    // =========================================
    // RÉSOLUTION
    // =========================================

    public static function resolve(string $collection): string
    {
        $collection = self::normalizeCollection($collection);

        return self::$directories[$collection] ??= rtrim(
            base_path("public/images/{$collection}/thumbnail"),
            '/\\'
        ) . DIRECTORY_SEPARATOR;
    }

    // =========================================
    // NORMALISATION
    // =========================================

    private static function normalizeCollection(string $collection): string
    {
        $collection = strtolower(trim($collection));

        if ($collection === '' || preg_match('/^[a-z0-9_-]+$/', $collection) !== 1)
        {
            throw new InvalidArgumentException(
                "Invalid thumbnail collection: {$collection}"
            );
        }

        return $collection;
    }
}