<?php
namespace App\Core;

class Functions
{
    public static function config(): array
    {
        static $config = null;

        if ($config === null)
        {
            $config = require __DIR__ . '/../Config/config.php';
        }

        return $config;
    }

    public static function basePath(): string
    {
        return self::config()['base_path'] ?? '/';
    }

    public static function siteName(): string
    {
        return self::config()['site_name'] ?? 'Site';
    }

    public static function pagination(): int
    {
        return (int) (self::config()['pagination'] ?? 8);
    }
}