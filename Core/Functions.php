<?php

declare(strict_types=1);

namespace App\Core;

class Functions
{
    /**
     * Cache mémoire des variables d'environnement déjà lues.
     */
    private static array $envCache = [];

    /**
     * Récupère une variable d'environnement.
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$envCache))
        {
            return self::$envCache[$key];
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null)
        {
            self::$envCache[$key] = $default;
            return $default;
        }

        if (is_string($value))
        {
            $value = trim($value);

            $value = match (strtolower($value))
            {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'null', '(null)' => null,
                'empty', '(empty)' => '',
                default => $value
            };
        }

        self::$envCache[$key] = $value;

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration application
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne le chemin de base du projet.
     */
    public static function basePath(): string
    {
        return (string) self::env('APP_BASE_PATH', '/');
    }

    /**
     * Retourne le nom du site.
     */
    public static function siteName(): string
    {
        return (string) self::env('APP_NAME', 'Site');
    }

    /**
     * Retourne le nombre d'éléments par page.
     */
    public static function pagination(): int
    {
        return max(1, (int) self::env('APP_PAGINATION', 8));
    }

    /**
     * Retourne l'environnement de l'application.
     */
    public static function appEnv(): string
    {
        return (string) self::env('APP_ENV', 'local');
    }

    /**
     * Retourne si le mode debug est activé.
     */
    public static function appDebug(): bool
    {
        return (bool) self::env('APP_DEBUG', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration base de données
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne l'hôte MySQL.
     */
    public static function dbHost(): string
    {
        return (string) self::env('DB_HOST', 'localhost');
    }

    /**
     * Retourne le nom de la base de données.
     */
    public static function dbName(): string
    {
        return (string) self::env('DB_NAME', '');
    }

    /**
     * Retourne l'utilisateur MySQL.
     */
    public static function dbUser(): string
    {
        return (string) self::env('DB_USER', '');
    }

    /**
     * Retourne le mot de passe MySQL.
     */
    public static function dbPass(): string
    {
        return (string) self::env('DB_PASS', '');
    }

    /**
     * Retourne le charset MySQL.
     */
    public static function dbCharset(): string
    {
        return (string) self::env('DB_CHARSET', 'utf8mb4');
    }

    /*
    |--------------------------------------------------------------------------
    | Requête HTTP
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifie si la requête courante est en POST.
     */
    public static function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    /*
    |--------------------------------------------------------------------------
    | Récupération données POST
    |--------------------------------------------------------------------------
    */

    /**
     * Récupère une chaîne depuis POST.
     */
    public static function postString(string $key): string
    {
        return trim((string) ($_POST[$key] ?? ''));
    }

    /**
     * Récupère un entier depuis POST.
     */
    public static function postInt(string $key): int
    {
        return (int) ($_POST[$key] ?? 0);
    }

    /**
     * Récupère une chaîne nullable depuis POST.
     * Retourne null si la valeur est vide.
     */
    public static function postNullableString(string $key): ?string
    {
        $value = trim((string) ($_POST[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Gestion des uploads
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifie si un fichier a été envoyé.
     */
    public static function hasUploadedFile(string $key): bool
    {
        return isset($_FILES[$key], $_FILES[$key]['error'])
            && (int) $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Alias de compatibilité.
     */
    public static function fileExists(string $key): bool
    {
        return self::hasUploadedFile($key);
    }

    /**
     * Retourne le nom original du fichier uploadé.
     */
    public static function fileName(string $key): ?string
    {
        if (!self::hasUploadedFile($key))
        {
            return null;
        }

        $name = $_FILES[$key]['name'] ?? null;

        return is_string($name) && $name !== '' ? $name : null;
    }

    /**
     * Retourne le chemin temporaire du fichier uploadé.
     */
    public static function fileTmp(string $key): ?string
    {
        if (!self::hasUploadedFile($key))
        {
            return null;
        }

        $tmp = $_FILES[$key]['tmp_name'] ?? null;

        return is_string($tmp) && $tmp !== '' ? $tmp : null;
    }

    /**
     * Retourne le code d'erreur du fichier uploadé.
     */
    public static function fileError(string $key): ?int
    {
        if (!isset($_FILES[$key]['error']))
        {
            return null;
        }

        return (int) $_FILES[$key]['error'];
    }

    /**
     * Retourne la taille du fichier uploadé.
     */
    public static function fileSize(string $key): ?int
    {
        if (!self::hasUploadedFile($key))
        {
            return null;
        }

        $size = $_FILES[$key]['size'] ?? null;

        if (is_int($size))
        {
            return $size;
        }

        if (is_string($size) && ctype_digit($size))
        {
            return (int) $size;
        }

        return null;
    }

    /**
     * Retourne l'extension du fichier uploadé en minuscules.
     */
    public static function fileExtension(string $key): ?string
    {
        $name = self::fileName($key);

        if ($name === null)
        {
            return null;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return $extension !== '' ? $extension : null;
    }

    /**
     * Retourne le type MIME réel d'un fichier uploadé.
     */
    public static function fileMimeType(string $key): ?string
    {
        $tmpName = self::fileTmp($key);

        if ($tmpName === null || !is_file($tmpName))
        {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false)
        {
            return null;
        }

        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        return is_string($mimeType) && $mimeType !== '' ? strtolower($mimeType) : null;
    }

    /**
     * Retourne la taille maximale autorisée pour un upload.
     */
    public static function uploadMaxSize(): int
    {
        return max(1, (int) self::env('UPLOAD_MAX_SIZE', 5242880));
    }

    /**
     * Retourne la liste des extensions autorisées.
     *
     * @return string[]
     */
    public static function uploadAllowedExtensions(): array
    {
        $extensions = (string) self::env('UPLOAD_ALLOWED_EXT', 'jpg,jpeg,png,webp');

        $extensions = explode(',', strtolower($extensions));
        $extensions = array_map('trim', $extensions);
        $extensions = array_filter($extensions);

        return array_values(array_unique($extensions));
    }

    /**
     * Retourne la liste des types MIME autorisés.
     *
     * @return string[]
     */
    public static function uploadAllowedMimeTypes(): array
    {
        $mimeTypes = (string) self::env(
            'UPLOAD_ALLOWED_MIME',
            'image/jpeg,image/png,image/webp'
        );

        $mimeTypes = explode(',', strtolower($mimeTypes));
        $mimeTypes = array_map('trim', $mimeTypes);
        $mimeTypes = array_filter($mimeTypes);

        return array_values(array_unique($mimeTypes));
    }

    /**
     * Retourne le dossier des thumbnails manga.
     */
    public static function mangaThumbnailDirectory(): string
    {
        return ROOT . '/public/images/mangas/thumbnail/';
    }

    /*
    |--------------------------------------------------------------------------
    | Normalisation
    |--------------------------------------------------------------------------
    */

    /**
     * Normalise un slug.
     */
    public static function normalizeSlug(string $slug): string
    {
        $slug = mb_strtolower(trim($slug), 'UTF-8');
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $slug) ?? '';
        $slug = preg_replace('/[\s-]+/u', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug;
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
     * Génère un nom de thumbnail propre.
     */
    public static function buildThumbnailName(string $livre, int $numero): string
    {
        $thumbnail = self::normalizeSlug($livre);

        if ($thumbnail === '' || $numero <= 0)
        {
            return '';
        }

        return $thumbnail . '-' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
    }
}