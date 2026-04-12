<?php
namespace App\Core;

class Functions
{
    /**
     * retourne toute la configuration
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
     * retourne le chemin de base du projet
     */
    public static function basePath(): string
    {
        $config = self::config();

        return $config['base_path'] ?? '/';
    }

    /**
     * retourne le nom du site
     */
    public static function siteName(): string
    {
        $config = self::config();

        return $config['site_name'] ?? 'Site';
    }

    /**
     * retourne le nombre d'éléments par page
     */
    public static function pagination(): int
    {
        $config = self::config();

        return (int) ($config['pagination'] ?? 8);
    }
}