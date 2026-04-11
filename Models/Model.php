<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOStatement;

class Model
{
    protected string $table;
    protected ?PDO $db = null;


    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    /**
     * récup 1 ligne par id
     */
    public function find(int $id): object|false
    {
        return $this->requete(
            "SELECT *
            FROM {$this->table}
            WHERE id = ?",
            [$id]
        )->fetch();
    }


    /**
     * récup 1 ligne par name
     */
    public function findName(string $name): object|false
    {
        return $this->requete(
            "SELECT *
            FROM {$this->table}
            WHERE name = ?",
            [trim($name)]
        )->fetch();
    }


    /**
     * récup plusieurs lignes selon conditions
     */
    public function findBy(array $targets): array
    {
        if (empty($targets))
        {
            return [];
        }

        $fields = [];
        $values = [];

        foreach ($targets as $field => $value)
        {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        $sql = "SELECT *
                FROM {$this->table}
                WHERE " . implode(' AND ', $fields);

        return $this->requete($sql, $values)->fetchAll();
    }


    /**
     * récup tout
     */
    public function findAll(string $orderBy = 'id DESC'): array
    {
        $orderBy = $this->sanitizeOrderBy($orderBy);

        return $this->requete(
            "SELECT *
            FROM {$this->table}
            ORDER BY {$orderBy}"
        )->fetchAll();
    }


    /**
     * alias findAll avec order by
     */
    public function findAllOrderBy(string $orderBy): array
    {
        return $this->findAll($orderBy);
    }


    /**
     * récup tout avec limite
     */
    public function findAllOrderByLimit(string $orderBy, int $limit): array
    {
        $orderBy = $this->sanitizeOrderBy($orderBy);
        $limit = max(1, $limit);

        return $this->requete(
            "SELECT *
            FROM {$this->table}
            ORDER BY {$orderBy}
            LIMIT {$limit}"
        )->fetchAll();
    }


    /**
     * pagination simple
     */
    public function findAllPaginate(string $orderBy, int $eachPerPage, int $page): array
    {
        $orderBy = $this->sanitizeOrderBy($orderBy);
        $eachPerPage = max(1, $eachPerPage);
        $page = max(1, $page);

        $start = ($page - 1) * $eachPerPage;

        return $this->requete(
            "SELECT *
            FROM {$this->table}
            ORDER BY {$orderBy}
            LIMIT {$start}, {$eachPerPage}"
        )->fetchAll();
    }


    /**
     * nb total de pages
     */
    public function countPaginate(int $eachPerPage): int
    {
        $eachPerPage = max(1, $eachPerPage);

        $query = $this->requete(
            "SELECT COUNT(*) AS total
            FROM {$this->table}"
        );

        $result = $query->fetch();

        return (int) ceil(($result->total ?? 0) / $eachPerPage);
    }


    /**
     * insert générique
     */
    public function insert(array $datas): bool
    {
        if (empty($datas))
        {
            return false;
        }

        $fields = array_keys($datas);
        $placeholders = array_fill(0, count($datas), '?');
        $values = array_values($datas);

        $sql = "INSERT INTO {$this->table} (
                    " . implode(', ', $fields) . "
                )
                VALUES (
                    " . implode(', ', $placeholders) . "
                )";

        return $this->requete($sql, $values) !== false;
    }


    /**
     * update générique
     */
    public function update(array $datas, array $where): bool
    {
        if (empty($datas) || empty($where))
        {
            return false;
        }

        $fields = [];
        $conditions = [];
        $values = [];

        foreach ($datas as $field => $value)
        {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        foreach ($where as $field => $value)
        {
            $conditions[] = "{$field} = ?";
            $values[] = $value;
        }

        $sql = "UPDATE {$this->table}
                SET " . implode(', ', $fields) . "
                WHERE " . implode(' AND ', $conditions);

        return $this->requete($sql, $values) !== false;
    }


    /**
     * delete par id
     */
    public function delete(int $id): bool
    {
        return $this->requete(
            "DELETE
            FROM {$this->table}
            WHERE id = ?",
            [$id]
        ) !== false;
    }


    /**
     * hydrate objet
     */
    public function hydrate(array $datas): static
    {
        foreach ($datas as $key => $value)
        {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method))
            {
                $this->$method($value);
            }
        }

        return $this;
    }


    /**
     * requête sql
     */
    protected function requete(string $sql, ?array $attributes = null): PDOStatement|false
    {
        if ($this->db === null)
        {
            $this->db = Database::getInstance();
        }

        if ($attributes !== null)
        {
            $query = $this->db->prepare($sql);
            $query->execute($attributes);

            return $query;
        }

        return $this->db->query($sql);
    }


    /**
     * sécurise order by
     */
    protected function sanitizeOrderBy(string $orderBy): string
    {
        $orderBy = trim($orderBy);

        if (preg_match('/^[a-zA-Z0-9_]+(\s+(ASC|DESC))?$/i', $orderBy))
        {
            return $orderBy;
        }

        return 'id DESC';
    }
}