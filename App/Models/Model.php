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
    protected string $table = '';

    protected PDO $db;

    public function __construct(
        Database $database,
    ) {
        $this->db = $database;
    }

    protected function table(): string
    {
        $table = $this->sanitizeIdentifier(
            $this->table,
        );

        if ($table === '') {
            throw new RuntimeException(
                'Nom de table invalide.',
            );
        }

        return $table;
    }

    protected function getTable(): string
    {
        return $this->table();
    }

    protected function sanitizeIdentifier(
        string $value,
    ): string {
        $cleaned = preg_replace(
            '/[^a-zA-Z0-9_]/',
            '',
            $value,
        );

        return is_string($cleaned)
            ? $cleaned
            : '';
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function query(
        string $sql,
        array $params = [],
    ): PDOStatement|false {
        try {
            $statement = $this->db->prepare(
                $sql,
            );

            if ($statement === false) {
                return false;
            }

            $statement->execute($params);

            return $statement;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Erreur SQL : '
                . $exception->getMessage(),
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
        $statement = $this->query(
            $sql,
            $params,
        );

        if ($statement === false) {
            return null;
        }

        if ($class !== null) {
            $statement->setFetchMode(
                PDO::FETCH_CLASS,
                $class,
            );
        }

        $result = $statement->fetch();

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
        $statement = $this->query(
            $sql,
            $params,
        );

        if ($statement === false) {
            return [];
        }

        if ($class !== null) {
            $statement->setFetchMode(
                PDO::FETCH_CLASS,
                $class,
            );
        }

        /** @var array<int, object> $results */
        $results = $statement->fetchAll();

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
     * @return array{
     *     conditions: array<int, string>,
     *     values: array<int, mixed>
     * }
     */
    protected function buildWhere(
        array $where,
    ): array {
        $conditions = [];

        $values = [];

        foreach ($where as $field => $value) {
            $field = $this->sanitizeIdentifier(
                $field,
            );

            if ($field === '') {
                continue;
            }

            $conditions[] = "{$field} = ?";

            $values[] = $value;
        }

        return [
            'conditions' => $conditions,
            'values' => $values,
        ];
    }

    /**
     * @param array<string, mixed> $where
     * @return array<int, object>
     */
    public function findBy(
        array $where,
        ?string $class = null,
    ): array {
        if ($where === []) {
            return [];
        }

        $builtWhere = $this->buildWhere(
            $where,
        );

        if ($builtWhere['conditions'] === []) {
            return [];
        }

        return $this->fetchAll(
            "SELECT *
            FROM {$this->table()}
            WHERE "
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
     * @return array{
     *     fields: array<int, string>,
     *     placeholders: array<int, string>,
     *     values: array<int, mixed>
     * }
     */
    protected function buildInsert(
        array $data,
    ): array {
        $fields = [];

        $placeholders = [];

        $values = [];

        foreach ($data as $field => $value) {
            $field = $this->sanitizeIdentifier(
                $field,
            );

            if ($field === '') {
                continue;
            }

            $fields[] = $field;

            $placeholders[] = '?';

            $values[] = $value;
        }

        return [
            'fields' => $fields,
            'placeholders' => $placeholders,
            'values' => $values,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(
        array $data,
    ): bool {
        if ($data === []) {
            return false;
        }

        $builtInsert = $this->buildInsert(
            $data,
        );

        if ($builtInsert['fields'] === []) {
            return false;
        }

        return $this->execute(
            "INSERT INTO {$this->table()} ("
            . implode(
                ', ',
                $builtInsert['fields'],
            )
            . ')
            VALUES ('
            . implode(
                ', ',
                $builtInsert['placeholders'],
            )
            . ')',
            $builtInsert['values'],
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

        foreach ($data as $field => $value) {
            $field = $this->sanitizeIdentifier(
                $field,
            );

            if ($field === '') {
                continue;
            }

            $fields[] = "{$field} = ?";

            $values[] = $value;
        }

        $builtWhere = $this->buildWhere(
            $where,
        );

        if (
            $fields === []
            || $builtWhere['conditions'] === []
        ) {
            return false;
        }

        return $this->execute(
            "UPDATE {$this->table()}
            SET "
            . implode(', ', $fields)
            . '
            WHERE '
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
        if ($where === []) {
            return false;
        }

        $builtWhere = $this->buildWhere(
            $where,
        );

        if ($builtWhere['conditions'] === []) {
            return false;
        }

        return $this->execute(
            "DELETE FROM {$this->table()}
            WHERE "
            . implode(
                ' AND ',
                $builtWhere['conditions'],
            ),
            $builtWhere['values'],
        );
    }
}