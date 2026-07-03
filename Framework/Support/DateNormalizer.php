<?php

declare(strict_types=1);

namespace Framework\Support;

use DateTime;

final class DateNormalizer
{
    public static function normalize(?string $date): ?string
    {
        if ($date === null)
        {
            return null;
        }

        $parsed = DateTime::createFromFormat('d/m/Y', $date);

        return $parsed !== false ? $parsed->format('Y-m-d') : $date;
    }
}
