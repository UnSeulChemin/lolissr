<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Application\App;
use Framework\Database\Database;

use LogicException;
use PDO;
use PDOStatement;
use RuntimeException;
use Throwable;
use stdClass;

abstract class Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    /**
     * Nom de la table SQL.
     */
    protected string $table = '';

    /**
     * Nom de table validé et mis en cache.
     */
    private ?string $resolvedTable = null;

    /**
     * Cache des identifiants SQL nettoyés.
     *
     * @var array<string, string>
     */
    private static array $identifierCache = [];

    public function __construct(
        protected Database $db
    ) {
    }

    protected function table(): string
    {
        return $this->resolvedTable ??= $this->resolveTable();
    }

    protected function guardWrite(): void
    {
        if (App::isTesting())
        {
            throw new LogicException('Écriture en base interdite pendant les tests.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS SQL
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<int|string, mixed> $params
     */
    protected function query(string $sql, array $params = []): PDOStatement|false
    {
        try
        {
            $statement = $this->db->prepare($sql);

            if ($statement === false)
            {
                return false;
            }

            $statement->execute($params);

            return $statement;
        }
        catch (Throwable $exception)
        {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * @template T of object
     *
     * @param array<int|string, mixed> $params
     * @param class-string<T>|null $class
     *
     * @return ($class is class-string<T> ? T|null : stdClass|null)
     */
    protected function fetchOne(string $sql, array $params = [], ?string $class = null): ?object
    {
        $statement = $this->query($sql, $params);

        if ($statement === false)
        {
            return null;
        }

        if ($class !== null)
        {
            $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        $result = $statement->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * @template T of object
     *
     * @param array<int|string, mixed> $params
     * @param class-string<T>|null $class
     *
     * @return ($class is class-string<T> ? list<T> : list<stdClass>)
     */
    protected function fetchAll(string $sql, array $params = [], ?string $class = null): array
    {
        $statement = $this->query($sql, $params);

        if ($statement === false)
        {
            return [];
        }

        if ($class !== null)
        {
            $statement->setFetchMode(PDO::FETCH_CLASS, $class);

            /** @var list<T> $results */
            $results = $statement->fetchAll();

            return $results;
        }

        /** @var list<stdClass> $results */
        $results = $statement->fetchAll();

        return $results;
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $this->guardWrite();

        return $this->query($sql, $params) !== false;
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD
    |--------------------------------------------------------------------------
    */

    /**
     * @template T of object
     *
     * @param class-string<T>|null $class
     *
     * @return ($class is class-string<T> ? T|null : stdClass|null)
     */
    public function find(int $id, ?string $class = null): ?object
    {
        return $this->fetchOne("SELECT * FROM {$this->table()} WHERE id = ? LIMIT 1", [$id], $class);
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
            array_merge(
                $values,
                $builtWhere['values']
            )
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

    /*
    |--------------------------------------------------------------------------
    | STATISTIQUES
    |--------------------------------------------------------------------------
    */

    protected function countRows(): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) AS total FROM {$this->table()}");

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) ($data['total'] ?? 0);
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function countWhere(string $where, array $params = []): int
    {
        $result =
            $this->fetchOne(
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
        mixed $default = 0,
    ): mixed
    {
        $result = $this->fetchOne($sql, $params);

        if ($result === null)
        {
            return $default;
        }

        $resultArray = (array) $result;

        return $resultArray[$field] ?? $default;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS INTERNES
    |--------------------------------------------------------------------------
    */

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

        return ['conditions' => $conditions, 'values' => $values];
    }

    private function resolveTable(): string
    {
        $table = $this->sanitizeIdentifier($this->table);

        if ($table === '')
        {
            throw new RuntimeException('Nom de table invalide.');
        }

        return $table;
    }

    private function sanitizeIdentifier(string $value): string
    {
        return self::$identifierCache[$value]
            ??= preg_replace(
                '/[^a-zA-Z0-9_]/',
                '',
                $value
            ) ?? '';
    }
}
