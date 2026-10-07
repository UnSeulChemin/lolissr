<?php

declare(strict_types=1);

namespace App\Support\Search;

use Framework\Http\Exceptions\ValidationException;

final class SearchQuery
{
    public const MAX_LENGTH = 200;

    public static function validate(string $query): string
    {
        if (mb_strlen($query, 'UTF-8') > self::MAX_LENGTH)
            throw new ValidationException(['q' => 'La recherche ne doit pas dépasser 200 caractères.']);
        return trim($query);
    }

    public static function containsPattern(string $query): string
    {
        // Explicit SQL escape works with MySQL and SQLite, including literal backslashes.
        return '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    }
}
