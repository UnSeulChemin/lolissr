<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOStatement;

class Model
{
    /**
     * Nom de la table.
     */
    protected string $table;

    /**
     * Connexion PDO.
     */
    protected ?PDO $db = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retourne le nom de table sécurisé.
     */
    protected function getTable(): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $this->table) ?? '';
    }

    /**
     * Retourne un nom de colonne sécurisé.
     */
    protected function cleanField(string $field): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $field) ?? '';
    }

    /**
     * Récupère une ligne par id.
     */
    public function find(int $id): object|false
    {
        $query = $this->requete(
            "SELECT *
            FROM {$this->getTable()}
            WHERE id = ?",
            [$id]
        );

        return $query ? $query->fetch() : false;
    }

    /**
     * Récupère plusieurs lignes selon des conditions.
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
            $field = $this->cleanField($field);

            if ($field === '')
            {
                continue;
            }

            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        if (empty($fields))
        {
            return [];
        }

        $sql = "SELECT *
                FROM {$this->getTable()}
                WHERE " . implode(' AND ', $fields);

        $query = $this->requete($sql, $values);

        return $query ? $query->fetchAll() : [];
    }

    /**
     * Insert générique.
     */
    public function insert(array $datas): bool
    {
        if (empty($datas))
        {
            return false;
        }

        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($datas as $field => $value)
        {
            $field = $this->cleanField($field);

            if ($field === '')
            {
                continue;
            }

            $fields[] = $field;
            $placeholders[] = '?';
            $values[] = $value;
        }

        if (empty($fields))
        {
            return false;
        }

        $sql = "INSERT INTO {$this->getTable()} (
                    " . implode(', ', $fields) . "
                )
                VALUES (
                    " . implode(', ', $placeholders) . "
                )";

        return $this->requete($sql, $values) !== false;
    }

    /**
     * Update générique.
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
            $field = $this->cleanField($field);

            if ($field === '')
            {
                continue;
            }

            $fields[] = "{$field} = ?";
            $values[] = $value;
        }

        foreach ($where as $field => $value)
        {
            $field = $this->cleanField($field);

            if ($field === '')
            {
                continue;
            }

            $conditions[] = "{$field} = ?";
            $values[] = $value;
        }

        if (empty($fields) || empty($conditions))
        {
            return false;
        }

        $sql = "UPDATE {$this->getTable()}
                SET " . implode(', ', $fields) . "
                WHERE " . implode(' AND ', $conditions);

        return $this->requete($sql, $values) !== false;
    }

    /**
     * Supprime une ligne par id.
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
     * Exécute une requête SQL.
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

            return $query->execute($attributes) ? $query : false;
        }

        return $this->db->query($sql);
    }
}