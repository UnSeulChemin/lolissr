<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BuildsQueries;
use App\Models\Concerns\InteractsWithDatabase;

use Framework\Database\Database;

use stdClass;

abstract class Model
{
    use BuildsQueries;
    use InteractsWithDatabase;

    protected string $table = '';

    private ?string $resolvedTable = null;

    public function __construct(protected Database $db)
    {
    }

    // =========================================
    // TABLE
    // =========================================

    protected function table(): string
    {
        return $this->resolvedTable ??= $this->resolveTable();
    }

    // =========================================
    // CRUD
    // =========================================

    /**
     * @template T of object
     *
     * @param class-string<T>|null $class
     *
     * @return ($class is class-string<T> ? T|null : stdClass|null)
     */
    public function find(int $id, ?string $class = null): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table()} WHERE id = ? LIMIT 1",
            [$id],
            $class
        );
    }

    /**
     * @template T of object
     *
     * @param array<string, mixed> $where
     * @param class-string<T>|null $class
     *
     * @return ($class is class-string<T> ? list<T> : list<stdClass>)
     */
    public function findBy(array $where, ?string $class = null): array
    {
        if ($where === [])
        {
            return [];
        }

        $builtWhere = $this->buildWhere($where);

        if ($builtWhere['conditions'] === [])
        {
            return [];
        }

        return $this->fetchAll(
            'SELECT * FROM '
            . $this->table()
            . ' WHERE '
            . implode(' AND ', $builtWhere['conditions']),
            $builtWhere['values'],
            $class
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): bool
    {
        if ($data === [])
        {
            return false;
        }

        $fields = [];
        $values = [];

        foreach ($data as $field => $value)
        {
            $field = $this->sanitizeIdentifier($field);

            if ($field === '')
            {
                continue;
            }

            $fields[] = $field;
            $values[] = $value;
        }

        if ($fields === [])
        {
            return false;
        }

        $placeholders = array_fill(0, count($fields), '?');

        return $this->execute(
            'INSERT INTO '
            . $this->table()
            . ' ('
            . implode(', ', $fields)
            . ') VALUES ('
            . implode(', ', $placeholders)
            . ')',
            $values
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    public function update(array $data, array $where): bool
    {
        if ($data === [] || $where === [])
        {
            return false;
        }

        $fields = [];
        $values = [];

        foreach ($data as $field => $value)
        {
            $field = $this->sanitizeIdentifier($field);

            if ($field === '')
            {
                continue;
            }

            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        $builtWhere = $this->buildWhere($where);

        if ($fields === [] || $builtWhere['conditions'] === [])
        {
            return false;
        }

        return $this->execute(
            'UPDATE '
            . $this->table()
            . ' SET '
            . implode(', ', $fields)
            . ' WHERE '
            . implode(' AND ', $builtWhere['conditions']),
            array_merge($values, $builtWhere['values'])
        );
    }

    /**
     * @param array<string, mixed> $where
     */
    public function delete(array $where): bool
    {
        if ($where === [])
        {
            return false;
        }

        $builtWhere = $this->buildWhere($where);

        if ($builtWhere['conditions'] === [])
        {
            return false;
        }

        return $this->execute(
            'DELETE FROM '
            . $this->table()
            . ' WHERE '
            . implode(' AND ', $builtWhere['conditions']),
            $builtWhere['values']
        );
    }

    // =========================================
    // STATISTIQUES
    // =========================================

    protected function countRows(): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM {$this->table()}"
        );

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) ($data['total'] ?? 0);
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function countWhere(string $where, array $params = []): int
    {
        $result = $this->fetchOne(
            "
            SELECT
                COUNT(*) AS total

            FROM {$this->table()}

            WHERE {$where}
            ",
            $params
        );

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) ($data['total'] ?? 0);
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function fetchSingleValue(
        string $sql,
        string $field,
        array $params = [],
        mixed $default = 0
    ): mixed {
        $result = $this->fetchOne($sql, $params);

        if ($result === null)
        {
            return $default;
        }

        $data = (array) $result;

        return $data[$field] ?? $default;
    }
}