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

    /**
     * retourne l'hôte mysql
     */
    public static function dbHost(): string
    {
        $config = self::config();
        return $config['db_host'] ?? 'localhost';
    }

    /**
     * retourne le nom de la base
     */
    public static function dbName(): string
    {
        $config = self::config();
        return $config['db_name'] ?? '';
    }

    /**
     * retourne l'utilisateur mysql
     */
    public static function dbUser(): string
    {
        $config = self::config();
        return $config['db_user'] ?? '';
    }

    /**
     * retourne le mot de passe mysql
     */
    public static function dbPass(): string
    {
        $config = self::config();
        return $config['db_pass'] ?? '';
    }
}