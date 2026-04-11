<?php

namespace App\Models;

use App\Core\Database;
use PDOStatement;

class Model
{
    protected string $table;
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): object|false
    {
        return $this->requete(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        )->fetch();
    }

    public function findName(string $name): object|false
    {
        return $this->requete(
            "SELECT * FROM {$this->table} WHERE name = ?",
            [trim($name)]
        )->fetch();
    }

    public function findBy(array $targets): array
    {
        if (empty($targets)) {
            return [];
        }

        $fields = [];
        $values = [];

        foreach ($targets as $field => $value) {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $fields);

        return $this->requete($sql, $values)->fetchAll();
    }

    public function findAll(string $orderBy = 'id DESC'): array
    {
        $orderBy = $this->sanitizeOrderBy($orderBy);

        return $this->requete(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy}"
        )->fetchAll();
    }

    public function findAllOrderBy(string $orderBy): array
    {
        return $this->findAll($orderBy);
    }

    public function findAllOrderByLimit(string $orderBy, int $limit): array
    {
        $orderBy = $this->sanitizeOrderBy($orderBy);
        $limit = max(1, $limit);

        return $this->requete(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT {$limit}"
        )->fetchAll();
    }

    public function findAllPaginate(string $orderBy, int $eachPerPage, int $page): array
    {
        $orderBy = $this->sanitizeOrderBy($orderBy);
        $eachPerPage = max(1, $eachPerPage);
        $page = max(1, $page);

        $start = ($page - 1) * $eachPerPage;

        return $this->requete(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} LIMIT {$start}, {$eachPerPage}"
        )->fetchAll();
    }

    public function countPaginate(int $eachPerPage): int
    {
        $eachPerPage = max(1, $eachPerPage);

        $query = $this->requete(
            "SELECT COUNT(*) AS total FROM {$this->table}"
        );

        $result = $query->fetch();

        return (int) ceil(($result->total ?? 0) / $eachPerPage);
    }

    public function insert(array $datas): bool
    {
        if (empty($datas)) {
            return false;
        }

        $fields = array_keys($datas);
        $placeholders = array_fill(0, count($datas), '?');
        $values = array_values($datas);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        return $this->requete($sql, $values) !== false;
    }

    public function delete(int $id): bool
    {
        return $this->requete(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        ) !== false;
    }

    public function hydrate(array $datas): static
    {
        foreach ($datas as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    protected function requete(string $sql, ?array $attributes = null): PDOStatement|false
    {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }

        if ($attributes !== null) {
            $query = $this->db->prepare($sql);
            $query->execute($attributes);
            return $query;
        }

        return $this->db->query($sql);
    }

    protected function sanitizeOrderBy(string $orderBy): string
    {
        $orderBy = trim($orderBy);

        if (preg_match('/^[a-zA-Z0-9_]+(\s+(ASC|DESC))?$/i', $orderBy)) {
            return $orderBy;
        }

        return 'id DESC';
    }
}