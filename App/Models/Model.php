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
    protected string $table;

    protected PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    protected function table(): string
    {
        return preg_replace(
            '/[^a-zA-Z0-9_]/',
            '',
            $this->table
        ) ?? '';
    }

    protected function getTable(): string
    {
        return $this->table();
    }

    protected function clean(
        string $field
    ): string {
        return preg_replace(
            '/[^a-zA-Z0-9_]/',
            '',
            $field
        ) ?? '';
    }

    protected function query(
        string $sql,
        array $params = []
    ): PDOStatement|false {
        try
        {
            $stmt = $this->db->prepare($sql);

            if ($stmt === false)
            {
                return false;
            }

            $stmt->execute($params);

            return $stmt;
        }
        catch (Throwable $e)
        {
            throw new RuntimeException(
                $e->getMessage()
            );
        }
    }

    protected function requete(
        string $sql,
        array $params = []
    ): PDOStatement|false {
        return $this->query($sql, $params);
    }

    protected function fetchOne(
        string $sql,
        array $params = []
    ): ?object {
        $stmt = $this->query($sql, $params);

        if ($stmt === false)
        {
            return null;
        }

        $result = $stmt->fetch();

        return $result !== false
            ? $result
            : null;
    }

    /**
     * @return array<int, object>
     */
    protected function fetchAll(
        string $sql,
        array $params = []
    ): array {
        $stmt = $this->query($sql, $params);

        if ($stmt === false)
        {
            return [];
        }

        return $stmt->fetchAll();
    }

    protected function execute(
        string $sql,
        array $params = []
    ): bool {
        return $this->query($sql, $params) !== false;
    }

    public function find(
        int $id
    ): ?object {
        return $this->fetchOne(
            "SELECT *
            FROM {$this->table()}
            WHERE id = ?
            LIMIT 1",
            [$id]
        );
    }

    /**
     * @param array<string, mixed> $where
     *
     * @return array{
     *     conditions: array<int, string>,
     *     values: array<int, mixed>
     * }
     */
    protected function buildWhere(
        array $where
    ): array {
        $conditions = [];
        $values = [];

        foreach ($where as $field => $value)
        {
            $field = $this->clean($field);

            if ($field === '')
            {
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
     *
     * @return array<int, object>
     */
    public function findBy(
        array $where
    ): array {
        if ($where === [])
        {
            return [];
        }

        $built = $this->buildWhere($where);

        if ($built['conditions'] === [])
        {
            return [];
        }

        return $this->fetchAll(
            "SELECT *
            FROM {$this->table()}
            WHERE " . implode(
                ' AND ',
                $built['conditions']
            ),
            $built['values']
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function buildInsert(
        array $data
    ): array {
        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $field => $value)
        {
            $field = $this->clean($field);

            if ($field === '')
            {
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
        array $data
    ): bool {
        if ($data === [])
        {
            return false;
        }

        $built = $this->buildInsert($data);

        if ($built['fields'] === [])
        {
            return false;
        }

        return $this->execute(
            "INSERT INTO {$this->table()} (
                " . implode(', ', $built['fields']) . "
            )
            VALUES (
                " . implode(', ', $built['placeholders']) . "
            )",
            $built['values']
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    public function update(
        array $data,
        array $where
    ): bool {
        if ($data === [] || $where === [])
        {
            return false;
        }

        $fields = [];
        $values = [];

        foreach ($data as $field => $value)
        {
            $field = $this->clean($field);

            if ($field === '')
            {
                continue;
            }

            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        $builtWhere = $this->buildWhere($where);

        if (
            $fields === []
            || $builtWhere['conditions'] === []
        ) {
            return false;
        }

        return $this->execute(
            "UPDATE {$this->table()}
            SET " . implode(', ', $fields) . "
            WHERE " . implode(
                ' AND ',
                $builtWhere['conditions']
            ),
            [
                ...$values,
                ...$builtWhere['values']
            ]
        );
    }

    /**
     * @param array<string, mixed> $where
     */
    public function delete(
        array $where
    ): bool {
        if ($where === [])
        {
            return false;
        }

        $built = $this->buildWhere($where);

        if ($built['conditions'] === [])
        {
            return false;
        }

        return $this->execute(
            "DELETE FROM {$this->table()}
            WHERE " . implode(
                ' AND ',
                $built['conditions']
            ),
            $built['values']
        );
    }
}