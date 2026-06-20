<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Application\App;
use Framework\Database\Database;

use PDO;
use PDOStatement;
use LogicException;
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

        return self::$identifierCache[$value]
            ??= preg_replace(
                '/[^a-zA-Z0-9_]/',
                '',
                $value,
            ) ?? '';
    }

    protected function guardWrite(): void
    {
        if (! App::isReadOnly())
        {
            return;
        }

        throw new LogicException(
            'Écriture en base interdite en mode lecture seule.',
        );
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
     * @template T of object
     *
     * @param class-string<T>|null $class
     *
     * @return T|null
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
     * @template T of object
     *
     * @param class-string<T>|null $class
     *
     * @return list<T>
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

        /** @var list<T> $results */
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

    /**
     * @template T of object
     * @param class-string<T>|null $class
     * @return T|null
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
     *
     * @template T of object
     * @param class-string<T>|null $class
     * @return list<T>
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
    | Aggregate Helpers
    |--------------------------------------------------------------------------
    */

    protected function countRows(): int
    {
        $result =
            $this->fetchOne(
                "
                SELECT COUNT(*) AS total
                FROM {$this->table()}
                ",
            );

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) (
            $data['total']
            ?? 0
        );
    }

    protected function countWhere(
        string $where,
        array $params = [],
    ): int {

        $result =
            $this->fetchOne(
                "
                SELECT
                    COUNT(*) AS total
                FROM {$this->table()}
                WHERE {$where}
                ",
                $params,
            );

        /** @var array{total?: mixed} $data */
        $data =
            (array) $result;

        return (int) (
            $data['total']
            ?? 0
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
