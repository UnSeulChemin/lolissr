<?php

declare(strict_types=1);

namespace Framework\Support\Dates;

use DateTimeImmutable;

final class DateNormalizer
{
    private const INPUT_FORMATS = ['Y-m-d', 'd/m/Y'];
    private const OUTPUT_FORMAT = 'Y-m-d';

    private function __construct()
    {
    }

    // =================================================
    // NORMALISATION
    // =================================================

    public static function normalize(?string $date): ?string
    {
        if ($date !== null && str_contains($date, "\0"))
        {
            return null;
        }

        $date = $date !== null ? trim($date) : '';

        if ($date === '')
        {
            return null;
        }

        foreach (self::INPUT_FORMATS as $format)
        {
            $parsed = DateTimeImmutable::createFromFormat('!' . $format, $date);
            $errors = DateTimeImmutable::getLastErrors();

            if ($parsed !== false
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
                && $parsed->format($format) === $date)
            {
                return $parsed->format(self::OUTPUT_FORMAT);
            }
        }

        return null;
    }
}
