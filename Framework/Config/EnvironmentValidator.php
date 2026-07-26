<?php

declare(strict_types=1);

namespace Framework\Config;

use RuntimeException;

final class EnvironmentValidator
{
    private const ENVIRONMENTS = ['local', 'testing', 'production'];

    private const REQUIRED_VARIABLES = [
        'APP_NAME',
        'APP_ENV',
        'APP_BASE_URI',
        'APP_TIMEZONE',
        'DB_HOST',
        'DB_PORT',
        'DB_NAME',
        'DB_USER',
    ];

    private const POSITIVE_INTEGER_VARIABLES = [
        'APP_PAGINATION',
        'DB_PORT',
        'DB_SLOW_QUERY_THRESHOLD',
        'UPLOAD_MAX_SIZE',
        'UPLOAD_MAX_WIDTH',
        'UPLOAD_MAX_HEIGHT',
        'UPLOAD_MAX_PIXELS',
        'CACHE_TTL',
    ];

    // =========================================
    // VALIDATION
    // =========================================

    public static function validate(): void
    {
        self::validateRequiredVariables();
        self::validateEnvironment();
        self::validateBaseUri();
        self::validateTimezone();
        self::validatePositiveIntegers();
        self::validateDatabasePort();
        self::validateUploads();
        self::validateProduction();
    }

    // =========================================
    // VARIABLES OBLIGATOIRES
    // =========================================

    private static function validateRequiredVariables(): void
    {
        foreach (self::REQUIRED_VARIABLES as $key)
        {
            if (! Env::has($key) || trim((string) Env::get($key, '')) === '')
            {
                throw new RuntimeException(
                    "Missing required environment variable: {$key}"
                );
            }
        }
    }

    // =========================================
    // ENVIRONNEMENT
    // =========================================

    private static function validateEnvironment(): void
    {
        $environment = strtolower(trim((string) Env::get('APP_ENV', '')));

        if (! in_array($environment, self::ENVIRONMENTS, true))
        {
            throw new RuntimeException(
                "Invalid APP_ENV value: {$environment}. Allowed values: "
                . implode(', ', self::ENVIRONMENTS)
            );
        }
    }

    private static function validateProduction(): void
    {
        if (strtolower((string) Env::get('APP_ENV', '')) !== 'production')
        {
            return;
        }

        if (Env::bool('APP_DEBUG', false))
        {
            throw new RuntimeException(
                'APP_DEBUG must be false in production.'
            );
        }

        if (Env::bool('PROFILER_ENABLED', false))
        {
            throw new RuntimeException(
                'PROFILER_ENABLED must be false in production.'
            );
        }

        if (Env::bool('SQL_TOOL_ENABLED', false))
        {
            throw new RuntimeException(
                'SQL_TOOL_ENABLED must be false in production.'
            );
        }

        if (Env::bool('REGISTRATION_ENABLED', false))
        {
            throw new RuntimeException(
                'REGISTRATION_ENABLED must be false in production.'
            );
        }
    }

    // =========================================
    // APPLICATION
    // =========================================

    private static function validateBaseUri(): void
    {
        $baseUri = trim((string) Env::get('APP_BASE_URI', ''));

        if ($baseUri === '/')
        {
            return;
        }

        if (
            ! str_starts_with($baseUri, '/')
            || str_ends_with($baseUri, '/')
            || str_contains($baseUri, '://')
            || preg_match('#^/[a-zA-Z0-9._~/-]+$#', $baseUri) !== 1
        )
        {
            throw new RuntimeException(
                "Invalid APP_BASE_URI value: {$baseUri}. "
                . 'Expected "/" or a path such as "/lolissr".'
            );
        }
    }

    private static function validateTimezone(): void
    {
        $timezone = trim((string) Env::get('APP_TIMEZONE', ''));

        if ($timezone === '' || ! in_array($timezone, timezone_identifiers_list(), true))
        {
            throw new RuntimeException(
                "Invalid APP_TIMEZONE value: {$timezone}"
            );
        }
    }

    // =========================================
    // VALEURS NUMÉRIQUES
    // =========================================

    private static function validatePositiveIntegers(): void
    {
        foreach (self::POSITIVE_INTEGER_VARIABLES as $key)
        {
            if (! Env::has($key))
            {
                continue;
            }

            $value = Env::get($key);

            if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value <= 0)
            {
                throw new RuntimeException(
                    "Environment variable {$key} must be a positive integer."
                );
            }
        }
    }

    private static function validateDatabasePort(): void
    {
        $port = Env::int('DB_PORT');

        if ($port < 1 || $port > 65535)
        {
            throw new RuntimeException(
                "Invalid DB_PORT value: {$port}. Expected a value between 1 and 65535."
            );
        }
    }

    // =========================================
    // UPLOADS
    // =========================================

    private static function validateUploads(): void
    {
        self::validateCsvList('UPLOAD_ALLOWED_EXT');
        self::validateCsvList('UPLOAD_ALLOWED_MIME');
    }

    private static function validateCsvList(string $key): void
    {
        if (! Env::has($key))
        {
            throw new RuntimeException(
                "Missing required environment variable: {$key}"
            );
        }

        $values = array_filter(
            array_map(
                static fn (string $value): string => trim($value),
                explode(',', (string) Env::get($key, ''))
            ),
            static fn (string $value): bool => $value !== ''
        );

        if ($values === [])
        {
            throw new RuntimeException(
                "Environment variable {$key} must contain at least one value."
            );
        }
    }
}