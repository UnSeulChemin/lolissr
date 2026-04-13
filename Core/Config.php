<?php

namespace App\Core;

class Config
{
    /**
     * Retourne toute la configuration.
     */
    public static function all(): array
    {
        static $config = null;

        if ($config === null)
        {
            $config = require ROOT . '/Config/config.php';
        }

        return $config;
    }

    /**
     * Retourne une valeur de configuration imbriquée.
     * Exemple : app.base_path / database.host
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::all();

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
     * Retourne le chemin de base du projet.
     */
    public static function basePath(): string
    {
        return (string) self::get('app.base_path', '/');
    }

    /**
     * Retourne le nom du site.
     */
    public static function siteName(): string
    {
        return (string) self::get('app.site_name', 'Site');
    }

    /**
     * Retourne le nombre d'éléments par page.
     */
    public static function pagination(): int
    {
        return max(1, (int) self::get('app.pagination', 8));
    }

    /**
     * Retourne l'environnement de l'application.
     */
    public static function appEnv(): string
    {
        return (string) self::get('app.env', 'local');
    }

    /**
     * Retourne si le mode debug est activé.
     */
    public static function appDebug(): bool
    {
        return (bool) self::get('app.debug', false);
    }

    /**
     * Retourne l'hôte MySQL.
     */
    public static function dbHost(): string
    {
        return (string) self::get('database.host', 'localhost');
    }

    /**
     * Retourne le nom de la base de données.
     */
    public static function dbName(): string
    {
        return (string) self::get('database.name', '');
    }

    /**
     * Retourne l'utilisateur MySQL.
     */
    public static function dbUser(): string
    {
        return (string) self::get('database.user', '');
    }

    /**
     * Retourne le mot de passe MySQL.
     */
    public static function dbPass(): string
    {
        return (string) self::get('database.pass', '');
    }

    /**
     * Retourne le charset MySQL.
     */
    public static function dbCharset(): string
    {
        return (string) self::get('database.charset', 'utf8mb4');
    }
}