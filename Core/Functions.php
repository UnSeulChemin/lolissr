<?php

namespace App\Core;

class Functions
{
    /**
     * Retourne toute la configuration.
     */
    public static function config(): array
    {
        static $config = null;

        if ($config === null)
        {
            $config = require ROOT . '/Config/config.php';
        }

        return $config;
    }

    /**
     * Retourne une valeur de config imbriquée.
     * Exemple : app.base_path / database.host
     */
    public static function getConfig(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::config();

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
        return (string) self::getConfig('app.base_path', '/');
    }

    /**
     * Retourne le nom du site.
     */
    public static function siteName(): string
    {
        return (string) self::getConfig('app.site_name', 'Site');
    }

    /**
     * Retourne le nombre d'éléments par page.
     */
    public static function pagination(): int
    {
        return max(1, (int) self::getConfig('app.pagination', 8));
    }

    /**
     * Retourne l'environnement de l'application.
     */
    public static function appEnv(): string
    {
        return (string) self::getConfig('app.env', 'local');
    }

    /**
     * Retourne si le debug est activé.
     */
    public static function appDebug(): bool
    {
        return (bool) self::getConfig('app.debug', false);
    }

    /**
     * Retourne l'hôte MySQL.
     */
    public static function dbHost(): string
    {
        return (string) self::getConfig('database.host', 'localhost');
    }

    /**
     * Retourne le nom de la base.
     */
    public static function dbName(): string
    {
        return (string) self::getConfig('database.name', '');
    }

    /**
     * Retourne l'utilisateur MySQL.
     */
    public static function dbUser(): string
    {
        return (string) self::getConfig('database.user', '');
    }

    /**
     * Retourne le mot de passe MySQL.
     */
    public static function dbPass(): string
    {
        return (string) self::getConfig('database.pass', '');
    }

    /**
     * Retourne le charset MySQL.
     */
    public static function dbCharset(): string
    {
        return (string) self::getConfig('database.charset', 'utf8mb4');
    }

    /**
     * Récupère une variable d'environnement.
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null)
        {
            return $default;
        }

        if (is_string($value))
        {
            return match (strtolower(trim($value)))
            {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'null', '(null)' => null,
                'empty', '(empty)' => '',
                default => $value
            };
        }

        return $value;
    }
}