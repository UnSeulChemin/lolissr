<?php

declare(strict_types=1);

namespace App\Core;

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
     * Vérifie si la requête courante est en POST.
     */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
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
}