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
    // DOSSIER
    // =========================================

    public static function resolve(string $folder): string
    {
        $folder = self::normalizeFolder($folder);

        return self::$directories[$folder] ??= rtrim(
            base_path("public/images/{$folder}/thumbnail"),
            '/\\'
        ) . DIRECTORY_SEPARATOR;
    }

    // =========================================
    // NORMALISATION
    // =========================================

    private static function normalizeFolder(string $folder): string
    {
        $folder = strtolower(trim($folder));

        if ($folder === '' || preg_match('/^[a-z0-9_-]+$/', $folder) !== 1)
        {
            throw new InvalidArgumentException(
                "Invalid thumbnail folder: {$folder}"
            );
        }

        return $folder;
    }
}