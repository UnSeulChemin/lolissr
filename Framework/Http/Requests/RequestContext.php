<?php

declare(strict_types=1);

namespace Framework\Http\Requests;

use Random\RandomException;

final class RequestContext
{
    private static ?string $requestId = null;

    private function __construct()
    {
    }

    // =================================================
    // REQUÊTE
    // =================================================

    public static function start(): void
    {
        self::$requestId = self::generateRequestId();
    }

    public static function requestId(): string
    {
        return self::$requestId ??= self::generateRequestId();
    }

    // =================================================
    // GÉNÉRATION
    // =================================================

    private static function generateRequestId(): string
    {
        try
        {
            return bin2hex(random_bytes(8));
        }
        catch (RandomException)
        {
            return uniqid('req_', true);
        }
    }
}