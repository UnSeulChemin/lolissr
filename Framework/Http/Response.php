<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Support\Logger;

use JsonException;
use RuntimeException;

final class Response
{
    private const JSON_ERROR_RESPONSE = '{"success":false,"message":"JSON encode error"}';

    private function __construct()
    {
    }

    // =========================================
    // RÉPONSE
    // =========================================

    public static function html(string $content, int $statusCode = 200): never
    {
        self::setStatusCode($statusCode);
        self::sendContentType('text/html');

        echo $content;

        exit;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $statusCode = 200): never
    {
        self::setStatusCode($statusCode);
        self::sendContentType('application/json');

        try
        {
            echo json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
                | JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException $exception)
        {
            Logger::exception(
                $exception,
                [
                    'type' => 'json_encode'
                ]
            );

            self::setStatusCode(500);

            echo self::JSON_ERROR_RESPONSE;
        }

        exit;
    }

    public static function redirect(string $url, int $statusCode = 302): never
    {
        if ($statusCode < 300 || $statusCode > 399)
        {
            throw new RuntimeException(
                "Invalid redirect status code: {$statusCode}."
            );
        }

        if (headers_sent($file, $line))
        {
            throw new RuntimeException(
                "Unable to redirect to \"{$url}\": headers already sent in {$file}:{$line}."
            );
        }

        header('Location: ' . $url, true, $statusCode);

        exit;
    }

    // =========================================
    // EN-TÊTES
    // =========================================

    private static function setStatusCode(int $statusCode): void
    {
        if (! headers_sent())
        {
            http_response_code($statusCode);
        }
    }

    private static function sendContentType(string $contentType): void
    {
        if (! headers_sent())
        {
            header("Content-Type: {$contentType}; charset=UTF-8", true);
        }
    }
}