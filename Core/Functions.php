<?php
namespace App\Core;

class Functions
{
    /**
     * retourne toute la config
     */
    public static function config(): array
    {
        static $config = null;

        if ($config === null)
        {
            $config = require __DIR__ . '/../Config/config.php';
        }

        return $config;
    }

    /**
     * retourne le base path
     */
    public static function basePath(): string
    {
        return self::config()['base_path'] ?? '/';
    }

    /**
     * retourne le nom du site
     */
    public static function siteName(): string
    {
        return self::config()['site_name'] ?? 'Site';
    }

    /**
     * retourne la pagination
     */
    public static function pagination(): int
    {
        return (int) (self::config()['pagination'] ?? 8);
    }
}