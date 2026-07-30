<?php

declare(strict_types=1);

namespace Framework\Support;

use DateTimeImmutable;
use Throwable;

final class DateFormatter
{
    private function __construct()
    {
    }

    // =========================================
    // FORMATAGE
    // =========================================

    public static function display(?string $date): ?string
    {
        $date = $date !== null ? trim($date) : '';

        if ($date === '')
        {
            return null;
        }

        try
        {
            return (new DateTimeImmutable($date))->format('d/m/Y');
        }
        catch (Throwable)
        {
            return null;
        }
    }
}