<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    /**
     * Retourne la méthode HTTP courante.
     */
    public static function method(): string
    {
        return strtoupper(trim((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')));
    }

    /**
     * Vérifie si la requête courante est en GET.
     */
    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    /**
     * Vérifie si la requête courante est en POST.
     */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * Retourne l'URI courante.
     */
    public static function uri(): string
    {
        return (string) ($_SERVER['REQUEST_URI'] ?? '/');
    }

    /**
     * Retourne le path courant sans query string.
     */
    public static function path(): string
    {
        $path = parse_url(self::uri(), PHP_URL_PATH);

        if (!is_string($path) || $path === '')
        {
            return '/';
        }

        return $path;
    }

    /**
     * Récupère une valeur brute depuis GET.
     */
    public static function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Récupère une chaîne depuis GET.
     */
    public static function queryString(string $key): string
    {
        return trim((string) ($_GET[$key] ?? ''));
    }

    /**
     * Récupère un entier depuis GET.
     */
    public static function queryInt(string $key): int
    {
        return (int) ($_GET[$key] ?? 0);
    }

    /**
     * Vérifie si une clé GET existe.
     */
    public static function hasQuery(string $key): bool
    {
        return array_key_exists($key, $_GET);
    }

    /**
     * Récupère une valeur brute depuis POST.
     */
    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

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

    /**
     * Vérifie si une clé POST existe.
     */
    public static function hasPost(string $key): bool
    {
        return array_key_exists($key, $_POST);
    }

    /**
     * Retourne toutes les données POST.
     *
     * @return array<string, mixed>
     */
    public static function allPost(): array
    {
        return $_POST;
    }

    /**
     * Retourne toutes les données GET.
     *
     * @return array<string, mixed>
     */
    public static function allQuery(): array
    {
        return $_GET;
    }

    /**
     * Retourne toutes les données FILES.
     *
     * @return array<string, mixed>
     */
    public static function allFiles(): array
    {
        return $_FILES;
    }

    /**
     * Vérifie si un fichier uploadé existe.
     */
    public static function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && is_array($_FILES[$key]);
    }

    /**
     * Retourne les données brutes d'un fichier uploadé.
     *
     * @return array<string, mixed>|null
     */
    public static function file(string $key): ?array
    {
        if (!self::hasFile($key))
        {
            return null;
        }

        $file = $_FILES[$key];

        return is_array($file) ? $file : null;
    }

    /**
     * Retourne le nom original du fichier uploadé.
     */
    public static function fileName(string $key): string
    {
        return (string) (self::file($key)['name'] ?? '');
    }

    /**
     * Retourne le type MIME du fichier uploadé.
     */
    public static function fileType(string $key): string
    {
        return (string) (self::file($key)['type'] ?? '');
    }

    /**
     * Retourne le chemin temporaire du fichier uploadé.
     */
    public static function fileTmpPath(string $key): string
    {
        return (string) (self::file($key)['tmp_name'] ?? '');
    }

    /**
     * Retourne la taille du fichier uploadé.
     */
    public static function fileSize(string $key): int
    {
        return (int) (self::file($key)['size'] ?? 0);
    }

    /**
     * Retourne le code d'erreur du fichier uploadé.
     */
    public static function fileError(string $key): int
    {
        return (int) (self::file($key)['error'] ?? UPLOAD_ERR_NO_FILE);
    }

    /**
     * Vérifie si le fichier uploadé est valide.
     */
    public static function hasValidFile(string $key): bool
    {
        return self::hasFile($key) && self::fileError($key) === UPLOAD_ERR_OK;
    }

    /**
     * Retourne une valeur du serveur.
     */
    public static function server(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }
}