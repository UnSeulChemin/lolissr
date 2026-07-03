<?php

declare(strict_types=1);

namespace Framework\Support;

use DateTime;

final class DateFormatter
{
    public static function display(?string $date): ?string
    {
        if ($date === null || trim($date) === '')
        {
            return null;
        }

        return (new DateTime($date))->format('d/m/Y');
    }
}
