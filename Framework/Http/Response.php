<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Support\Logger;
use JsonException;

final class Response
{
    private static function setStatusCode(
        int $statusCode,
    ): void {

        if (! headers_sent()) {

            http_response_code(
                $statusCode,
            );
        }
    }

    private static function sendContentType(
        string $contentType,
    ): void {

        if (! headers_sent()) {
            header(
                "Content-Type: {$contentType}; charset=UTF-8",
            );
        }
    }

    public static function html(
        string $content,
        int $statusCode = 200,
    ): never {

        self::setStatusCode(
            $statusCode,
        );

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

        self::setStatusCode(
            $statusCode,
        );

        self::sendContentType(
            'application/json',
        );

        try {

            echo json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
                | JSON_THROW_ON_ERROR,
            );

        } catch (JsonException $exception) {

            Logger::exception(
                $exception,
                [
                    'type' =>
                        'json_encode',
                ],
            );

            self::setStatusCode(
                500,
            );

            echo json_encode(
                [
                    'success' =>
                        false,

                    'message' =>
                        'JSON encode error',
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE,
            );
        }

        exit;
    }

    public static function redirect(
        string $url,
        int $statusCode = 302,
    ): never {

        if (! headers_sent()) {

            header(
                'Location: ' . $url,
                true,
                $statusCode,
            );

            exit;
        }

        echo sprintf(
            '<script>window.location.href=%s;</script>',
            json_encode(
                $url,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES,
            ),
        );

        exit;
    }
}