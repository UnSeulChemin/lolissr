<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Response
{
    /**
     * Définit le code HTTP.
     */
    public static function status(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Envoie une redirection HTTP.
     */
    public static function redirect(string $url, int $code = 302): void
    {
        self::status($code);

        header('Location: ' . $url);
        exit;
    }

    /**
     * Envoie une réponse HTML.
     */
    public static function html(string $content, int $code = 200): void
    {
        self::status($code);

        header('Content-Type: text/html; charset=utf-8');

        echo $content;
        exit;
    }

    /**
     * Envoie une réponse JSON.
     *
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $code = 200): void
    {
        self::status($code);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        exit;
    }

    /**
     * Envoie une réponse vide.
     */
    public static function noContent(): void
    {
        self::status(204);
        exit;
    }

    /**
     * Envoie une erreur texte simple.
     */
    public static function text(string $content, int $code = 200): void
    {
        self::status($code);

        header('Content-Type: text/plain; charset=utf-8');

        echo $content;
        exit;
    }
}