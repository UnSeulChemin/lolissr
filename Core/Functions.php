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
     * Retourne une valeur de configuration imbriquée.
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
     * Retourne si le mode debug est activé.
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
     * Retourne le nom de la base de données.
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
     * Récupère une string depuis POST et la nettoie.
     */
    public static function postString(string $key): string
    {
        return trim($_POST[$key] ?? '');
    }

    /**
     * Récupère un entier depuis POST.
     */
    public static function postInt(string $key): int
    {
        return (int) ($_POST[$key] ?? 0);
    }

    /**
     * Récupère une string nullable depuis POST.
     * Retourne null si la valeur est vide.
     */
    public static function postNullableString(string $key): ?string
    {
        $value = trim($_POST[$key] ?? '');

        return $value === '' ? null : $value;
    }

    /**
     * Vérifie si un fichier uploadé existe.
     */
    public static function fileExists(string $key): bool
    {
        return isset($_FILES[$key])
            && isset($_FILES[$key]['error'])
            && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Retourne le nom original du fichier uploadé.
     */
    public static function fileName(string $key): ?string
    {
        if (!self::fileExists($key))
        {
            return null;
        }

        return $_FILES[$key]['name'] ?? null;
    }

    /**
     * Retourne le chemin temporaire du fichier uploadé.
     */
    public static function fileTmp(string $key): ?string
    {
        if (!self::fileExists($key))
        {
            return null;
        }

        return $_FILES[$key]['tmp_name'] ?? null;
    }

    /**
     * Retourne le code d'erreur du fichier uploadé.
     */
    public static function fileError(string $key): ?int
    {
        if (!isset($_FILES[$key]))
        {
            return null;
        }

        return $_FILES[$key]['error'] ?? null;
    }

    /**
     * Retourne la taille du fichier uploadé.
     */
    public static function fileSize(string $key): ?int
    {
        if (!self::fileExists($key))
        {
            return null;
        }

        return isset($_FILES[$key]['size']) ? (int) $_FILES[$key]['size'] : null;
    }

    /**
     * Retourne l'extension du fichier uploadé.
     */
    public static function fileExtension(string $key): ?string
    {
        $name = self::fileName($key);

        if ($name === null)
        {
            return null;
        }

        return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }

    /**
     * Normalise un slug.
     */
    public static function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);

        return trim($slug, '-');
    }

    /**
     * Nettoie un commentaire.
     * Retourne null si vide.
     */
    public static function normalizeCommentaire(?string $commentaire): ?string
    {
        if ($commentaire === null)
        {
            return null;
        }

        $commentaire = trim($commentaire);

        return $commentaire === '' ? null : $commentaire;
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