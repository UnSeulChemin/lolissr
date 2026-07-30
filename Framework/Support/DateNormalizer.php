<?php

declare(strict_types=1);

namespace Framework\Support;

use DateTimeImmutable;

final class DateNormalizer
{
    private const INPUT_FORMAT = 'd/m/Y';
    private const OUTPUT_FORMAT = 'Y-m-d';

    private function __construct()
    {
    }

    // =========================================
    // NORMALISATION
    // =========================================

    public static function normalize(?string $date): ?string
    {
        $date = $date !== null ? trim($date) : '';

        if ($date === '')
        {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat(
            '!' . self::INPUT_FORMAT,
            $date
        );

        if ($parsed === false)
        {
            return null;
        }

        $errors = DateTimeImmutable::getLastErrors();
        $warningCount = is_array($errors) ? $errors['warning_count'] : 0;
        $errorCount = is_array($errors) ? $errors['error_count'] : 0;

        if (
            $warningCount !== 0
            || $errorCount !== 0
            || $parsed->format(self::INPUT_FORMAT) !== $date
        ) {
            return null;
        }

        return $parsed->format(self::OUTPUT_FORMAT);
    }
}