<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Support\Logger;
use JsonException;

final class Response
{
    private static function sendContentType(
        string $contentType,
    ): void {

        if (headers_sent()) {
            return;
        }

        header(
            "Content-Type: {$contentType}; charset=UTF-8",
        );
    }

    public static function html(
        string $content,
        int $statusCode = 200,
    ): never {

        if (! headers_sent()) {
            http_response_code($statusCode);
        }

        self::sendContentType(
            'text/html',
        );

        echo $content;

        exit;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function json(
        array $data,
        int $statusCode = 200,
    ): never {

        if (! headers_sent()) {
            http_response_code($statusCode);
        }

        self::sendContentType(
            'application/json',
        );

        try {

            echo json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR,
            );

        } catch (JsonException $exception) {

            Logger::exception(
                $exception,
                [
                    'type' => 'json_encode',
                ],
            );

            if (! headers_sent()) {
                http_response_code(500);
            }

            echo '{"success":false,"message":"JSON encode error"}';
        }

        exit;
    }

    public static function redirect(
        string $url,
        int $statusCode = 302,
    ): never {

        if (! headers_sent()) {

            http_response_code($statusCode);

            header(
                'Location: ' . $url,
            );
        }

        exit;
    }
}