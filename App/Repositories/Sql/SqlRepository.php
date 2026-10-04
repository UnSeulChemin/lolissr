<?php

declare(strict_types=1);

namespace App\Repositories\Sql;

use App\Repositories\AbstractRepository;

final class SqlRepository extends AbstractRepository
{
    private const MAX_RESULT_ROWS = 500;

    /** @return list<int> */
    public function dashboardUserIds(): array
    {
        $rows = $this->fetchAll('SELECT id FROM users');
        return array_map(static fn (object $row): int => (int) $row->id, $rows);
    }

    /**
     * @return array{result: list<object>, truncated: bool, limit: int}
     */
    public function executeQuery(string $sql): array
    {
        $buffered = $this->db->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
        $this->db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $statement = null;

        try
        {
            $statement = $this->query(trim($sql));
            if ($statement === false)
            {
                throw new \RuntimeException('Impossible d’exécuter la requête SQL.');
            }

            $rows = [];
            $truncated = false;
            if ($statement->columnCount() > 0)
            {
                while (($row = $statement->fetchObject()) !== false)
                {
                    if (count($rows) === self::MAX_RESULT_ROWS)
                    {
                        $truncated = true;
                        break;
                    }
                    $rows[] = $row;
                }
            }

            return ['result' => $rows, 'truncated' => $truncated, 'limit' => self::MAX_RESULT_ROWS];
        }
        finally
        {
            try
            {
                if ($statement instanceof \PDOStatement) $statement->closeCursor();
            }
            finally
            {
                $this->db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $buffered);
            }
        }
    }
}
