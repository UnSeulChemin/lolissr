<?php

declare(strict_types=1);

namespace App\Core\Http;

use JsonException;

final class Response
{
    private static function sendContentType(
        string $contentType
    ): void {
        if (!headers_sent())
        {
            header(
                "Content-Type: {$contentType}; charset=UTF-8"
            );
        }
    }

    public static function html(
        string $content,
        int $statusCode = 200
    ): never {
        http_response_code($statusCode);

        self::sendContentType(
            'text/html'
        );

        echo $content;

        exit;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function json(
        array $data,
        int $statusCode = 200
    ): never {
        http_response_code($statusCode);

        self::sendContentType(
            'application/json'
        );

        try
        {
            echo json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException)
        {
            echo json_encode([
                'success' => false,
                'message' => 'JSON encode error',
            ]);
        }

        exit;
    }

    public static function redirect(
        string $url,
        int $statusCode = 302
    ): never {
        http_response_code($statusCode);

        if (!headers_sent())
        {
            header(
                'Location: ' . $url
            );
        }

        exit;
    }
}