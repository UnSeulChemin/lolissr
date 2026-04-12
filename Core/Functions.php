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
     * retourne une valeur de config imbriquée
     * ex: app.base_path / database.host
     */
    public static function getConfig(string $key, mixed $default = null): mixed
    {
        $config = self::config();
        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment)
        {
            if (!is_array($value) || !array_key_exists($segment, $value))
            {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * retourne le chemin de base du projet
     */
    public static function basePath(): string
    {
        return (string) self::getConfig('app.base_path', '/');
    }

    /**
     * retourne le nom du site
     */
    public static function siteName(): string
    {
        return (string) self::getConfig('app.site_name', 'Site');
    }

    /**
     * retourne le nombre d'éléments par page
     */
    public static function pagination(): int
    {
        return (int) self::getConfig('app.pagination', 8);
    }

    /**
     * retourne l'environnement de l'application
     */
    public static function appEnv(): string
    {
        return (string) self::getConfig('app.env', 'local');
    }

    /**
     * retourne si le debug est activé
     */
    public static function appDebug(): bool
    {
        return (bool) self::getConfig('app.debug', false);
    }

    /**
     * retourne l'hôte mysql
     */
    public static function dbHost(): string
    {
        return (string) self::getConfig('database.host', 'localhost');
    }

    /**
     * retourne le nom de la base
     */
    public static function dbName(): string
    {
        return (string) self::getConfig('database.name', '');
    }

    /**
     * retourne l'utilisateur mysql
     */
    public static function dbUser(): string
    {
        return (string) self::getConfig('database.user', '');
    }

    /**
     * retourne le mot de passe mysql
     */
    public static function dbPass(): string
    {
        return (string) self::getConfig('database.pass', '');
    }

    /**
     * retourne le charset mysql
     */
    public static function dbCharset(): string
    {
        return (string) self::getConfig('database.charset', 'utf8mb4');
    }

    /**
     * récupère une variable d'environnement
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $default;

        if (is_string($value))
        {
            return match (strtolower($value))
            {
                'true' => true,
                'false' => false,
                'null' => null,
                default => $value
            };
        }

        return $value;
    }
}