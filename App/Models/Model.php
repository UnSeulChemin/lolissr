<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Database\Database;
use PDO;
use PDOStatement;
use RuntimeException;
use Throwable;

abstract class Model
{
    /*
    |--------------------------------------------------------------------------
    | Table
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
        protected Database $db,
    ) {
    }

    /**
     * Retourne le nom de table validé.
     */
    protected function table(): string
    {
        return $this->resolvedTable
            ??= $this->resolveTable();
    }

    /**
     * Valide le nom de table configuré.
     */
    private function resolveTable(): string
    {
        $table =
            $this->sanitizeIdentifier(
                $this->table,
            );

        if ($table === '')
        {
            throw new RuntimeException(
                'Nom de table invalide.',
            );
        }

        return $table;
    }

    private function sanitizeIdentifier(
        string $value,
    ): string {

        $sanitized =
            preg_replace(
                '/[^a-zA-Z0-9_]/',
                '',
                $value,
            );

        return self::$identifierCache[$value]
            ??= ($sanitized ?? '');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<int|string, mixed> $params
     */
    protected function query(
        string $sql,
        array $params = [],
    ): PDOStatement|false {

        try
        {
            $statement =
                $this->db->prepare(
                    $sql,
                );

            if ($statement === false)
            {
                return false;
            }

            $statement->execute(
                $params,
            );

            return $statement;
        }
        catch (Throwable $exception)
        {
            throw new RuntimeException(
                $exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function fetchOne(
        string $sql,
        array $params = [],
        ?string $class = null,
    ): ?object {

        $statement =
            $this->query(
                $sql,
                $params,
            );

        if ($statement === false)
        {
            return null;
        }

        if ($class !== null)
        {
            $statement->setFetchMode(
                PDO::FETCH_CLASS,
                $class,
            );
        }

        $result =
            $statement->fetch();

        return $result !== false
            ? $result
            : null;
    }

    /**
     * @param array<int|string, mixed> $params
     * @return array<int, object>
     */
    protected function fetchAll(
        string $sql,
        array $params = [],
        ?string $class = null,
    ): array {

        $statement =
            $this->query(
                $sql,
                $params,
            );

        if ($statement === false)
        {
            return [];
        }

        if ($class !== null)
        {
            $statement->setFetchMode(
                PDO::FETCH_CLASS,
                $class,
            );
        }

        /** @var array<int, object> $results */
        $results =
            $statement->fetchAll();

        return $results;
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function execute(
        string $sql,
        array $params = [],
    ): bool {

        return $this->query(
            $sql,
            $params,
        ) !== false;
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD
    |--------------------------------------------------------------------------
    */

    public function find(
        int $id,
        ?string $class = null,
    ): ?object {

        return $this->fetchOne(
            "SELECT *
             FROM {$this->table()}
             WHERE id = ?
             LIMIT 1",
            [$id],
            $class,
        );
    }

    /**
     * @param array<string, mixed> $where
     * @return array<int, object>
     */
    public function findBy(
        array $where,
        ?string $class = null,
    ): array {

        if ($where === [])
        {
            return [];
        }

        $builtWhere =
            $this->buildWhere(
                $where,
            );

        if ($builtWhere['conditions'] === [])
        {
            return [];
        }

        return $this->fetchAll(
            'SELECT * FROM '
            . $this->table()
            . ' WHERE '
            . implode(
                ' AND ',
                $builtWhere['conditions'],
            ),
            $builtWhere['values'],
            $class,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(
        array $data,
    ): bool {

        if ($data === [])
        {
            return false;
        }

        $fields = [];
        $values = [];

        foreach ($data as $field => $value)
        {
            $field =
                $this->sanitizeIdentifier(
                    $field,
                );

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

        $placeholders =
            array_fill(
                0,
                count($fields),
                '?',
            );

        return $this->execute(
            'INSERT INTO '
            . $this->table()
            . ' ('
            . implode(', ', $fields)
            . ') VALUES ('
            . implode(', ', $placeholders)
            . ')',
            $values,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    public function update(
        array $data,
        array $where,
    ): bool {

        if (
            $data === []
            || $where === []
        ) {
            return false;
        }

        $fields = [];
        $values = [];

        foreach ($data as $field => $value)
        {
            $field =
                $this->sanitizeIdentifier(
                    $field,
                );

            if ($field === '')
            {
                continue;
            }

            $fields[] =
                "{$field} = ?";

            $values[] =
                $value;
        }

        $builtWhere =
            $this->buildWhere(
                $where,
            );

        if (
            $fields === []
            || $builtWhere['conditions'] === []
        ) {
            return false;
        }

        return $this->execute(
            'UPDATE '
            . $this->table()
            . ' SET '
            . implode(', ', $fields)
            . ' WHERE '
            . implode(
                ' AND ',
                $builtWhere['conditions'],
            ),
            array_merge(
                $values,
                $builtWhere['values'],
            ),
        );
    }

    /**
     * @param array<string, mixed> $where
     */
    public function delete(
        array $where,
    ): bool {

        if ($where === [])
        {
            return false;
        }

        $builtWhere =
            $this->buildWhere(
                $where,
            );

        if ($builtWhere['conditions'] === [])
        {
            return false;
        }

        return $this->execute(
            'DELETE FROM '
            . $this->table()
            . ' WHERE '
            . implode(
                ' AND ',
                $builtWhere['conditions'],
            ),
            $builtWhere['values'],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string, mixed> $where
     * @return array{
     *     conditions:list<string>,
     *     values:list<mixed>
     * }
     */
    private function buildWhere(
        array $where,
    ): array {

        $conditions = [];
        $values = [];

        foreach ($where as $field => $value)
        {
            $field =
                $this->sanitizeIdentifier(
                    $field,
                );

            if ($field === '')
            {
                continue;
            }

            $conditions[] =
                "{$field} = ?";

            $values[] =
                $value;
        }

        return [
            'conditions' => $conditions,
            'values' => $values,
        ];
    }
}
