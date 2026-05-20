<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database\Database;
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
        $table = preg_replace(
            '/[^a-zA-Z0-9_]/',
            '',
            $this->table,
        ) ?? '';

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

    protected function clean(
        string $field,
    ): string {
        return preg_replace(
            '/[^a-zA-Z0-9_]/',
            '',
            $field,
        ) ?? '';
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
        } catch (Throwable $e) {
            throw new RuntimeException(
                $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function requete(
        string $sql,
        array $params = [],
    ): PDOStatement|false {
        return $this->query(
            $sql,
            $params,
        );
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
            $field = $this->clean($field);

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

        $built = $this->buildWhere($where);

        if ($built['conditions'] === []) {
            return [];
        }

        return $this->fetchAll(
            "SELECT *
            FROM {$this->table()}
            WHERE "
            . implode(
                ' AND ',
                $built['conditions'],
            ),
            $built['values'],
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
            $field = $this->clean($field);

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

        $built = $this->buildInsert($data);

        if ($built['fields'] === []) {
            return false;
        }

        return $this->execute(
            "INSERT INTO {$this->table()} ("
            . implode(', ', $built['fields'])
            . ')
            VALUES ('
            . implode(
                ', ',
                $built['placeholders'],
            )
            . ')',
            $built['values'],
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
            $field = $this->clean($field);

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

        $built = $this->buildWhere($where);

        if ($built['conditions'] === []) {
            return false;
        }

        return $this->execute(
            "DELETE FROM {$this->table()}
            WHERE "
            . implode(
                ' AND ',
                $built['conditions'],
            ),
            $built['values'],
        );
    }
}
