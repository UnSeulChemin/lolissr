<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOStatement;

class Model
{
    /**
     * nom de la table
     */
    protected string $table;

    /**
     * connexion PDO
     */
    protected ?PDO $db = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * sécurise le nom de table
     */
    protected function getTable(): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);
    }

    /**
     * récup 1 ligne par id
     */
    public function find(int $id): object|false
    {
        return $this->requete(
            "SELECT *
             FROM {$this->getTable()}
             WHERE id = ?",
            [$id]
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
                FROM {$this->getTable()}
                WHERE " . implode(' AND ', $fields);

        return $this->requete($sql, $values)->fetchAll();
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

        $sql = "INSERT INTO {$this->getTable()} (
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

        $sql = "UPDATE {$this->getTable()}
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
             FROM {$this->getTable()}
             WHERE id = ?",
            [$id]
        ) !== false;
    }

    /**
     * requête SQL
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

            if ($query === false)
            {
                return false;
            }

            if (!$query->execute($attributes))
            {
                return false;
            }

            return $query;
        }

        return $this->db->query($sql);
    }
}