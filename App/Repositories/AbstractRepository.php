<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Concerns\BuildsQueries;
use App\Repositories\Concerns\InteractsWithDatabase;

use Framework\Database\Database;

abstract class AbstractRepository
{
    use BuildsQueries;
    use InteractsWithDatabase;

    protected string $table = '';

    private ?string $resolvedTable = null;

    private const OWNED_TABLES = [
        'manga', 'artbook', 'figurine', 'nendoroid', 'peluche',
        'chinois_grammaire', 'chinois_vocabulaire'
    ];

    public function __construct(protected Database $db)
    {
    }

    // =================================================
    // TABLE
    // =================================================

    protected function table(): string
    {
        return $this->resolvedTable ??= $this->resolveTable();
    }

    protected function userId(): int
    {
        $user = function_exists('user') ? user() : null;
        return $user === null ? 0 : $user->id;
    }

    protected function ownerCondition(): string
    {
        return 'user_id = ' . $this->userId();
    }

    protected function readTable(string $alias = ''): string
    {
        return $this->ownedTable($this->table(), $alias);
    }

    // Une source explicitement filtree conserve le scope dans les sous-requetes,
    // les CTE, les agregats et les recherches avec plusieurs branches OR.
    protected function ownedTable(string $table, string $alias = '', ?int $userId = null): string
    {
        if (! in_array($table, self::OWNED_TABLES, true))
        {
            throw new \LogicException('Table privee inconnue.');
        }
        $alias = $alias === '' ? $table : $this->sanitizeIdentifier($alias);
        $userId ??= $this->userId();
        return "(SELECT * FROM {$table} WHERE user_id = {$userId}) {$alias}";
    }

    private function ownsTable(): bool
    {
        return in_array($this->table(), self::OWNED_TABLES, true);
    }

    // =================================================
    // CRUD
    // =================================================

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): bool
    {
        if ($data === []) return false;
        if ($this->ownsTable())
        {
            $userId = $this->userId();
            if ($userId <= 0) throw new \LogicException('Une connexion est requise pour ajouter un element.');
            $data['user_id'] = $userId;
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
        if ($data === [] || $where === []) return false;
        if ($this->ownsTable())
        {
            unset($data['user_id']);
            $where['user_id'] = $this->userId();
        }
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
            array_merge($values, $builtWhere['values'])
        );
    }

    /**
     * @param array<string, mixed> $where
     */
    public function delete(array $where): bool
    {
        if ($where === []) return false;
        if ($this->ownsTable()) $where['user_id'] = $this->userId();

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

    protected function deleteExistingById(int $id): bool
    {
        $this->guardWrite();
        $statement = $this->query(
            "DELETE FROM {$this->table()} WHERE id = :id AND {$this->ownerCondition()}",
            ['id' => $id]
        );

        if ($statement === false)
        {
            throw new \RuntimeException('Impossible de supprimer cet élément.');
        }

        return $statement->rowCount() === 1;
    }

    // =================================================
    // STATISTIQUES
    // =================================================

    protected function countRows(): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM {$this->readTable()}"
        );

        /** @var array{total?: mixed} $data */
        $data = (array) $result;

        return (int) ($data['total'] ?? 0);
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function fetchSingleValue(string $sql, string $field, array $params = [], mixed $default = 0): mixed
    {
        $result = $this->fetchOne($sql, $params);

        if ($result === null)
        {
            return $default;
        }

        $data = (array) $result;

        return $data[$field] ?? $default;
    }
}
