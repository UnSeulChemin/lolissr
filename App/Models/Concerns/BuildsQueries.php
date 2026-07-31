<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use RuntimeException;

trait BuildsQueries
{
    /**
     * @var array<string, string>
     */
    private static array $identifierCache = [];

    // =========================================
    // CLAUSES
    // =========================================

    /**
     * @param array<string, mixed> $where
     *
     * @return array{
     *     conditions: list<string>,
     *     values: list<mixed>
     * }
     */
    private function buildWhere(array $where): array
    {
        $conditions = [];
        $values = [];

        foreach ($where as $field => $value)
        {
            $field = $this->sanitizeIdentifier($field);

            if ($field === '')
            {
                continue;
            }

            $conditions[] = "{$field} = ?";
            $values[] = $value;
        }

        return [
            'conditions' => $conditions,
            'values' => $values
        ];
    }

    // =========================================
    // TABLE
    // =========================================

    private function resolveTable(): string
    {
        $table = $this->sanitizeIdentifier($this->table);

        if ($table === '')
        {
            throw new RuntimeException(
                'Nom de table invalide.'
            );
        }

        return $table;
    }

    // =========================================
    // IDENTIFIANTS
    // =========================================

    private function sanitizeIdentifier(string $value): string
    {
        return self::$identifierCache[$value] ??= preg_replace(
            '/[^a-zA-Z0-9_]/',
            '',
            $value
        ) ?? '';
    }
}