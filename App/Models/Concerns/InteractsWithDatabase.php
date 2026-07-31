<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Framework\Application\App;
use Framework\Config\DatabaseConfig;
use Framework\Debug\Profiler;
use Framework\Support\Logger;

use LogicException;
use PDO;
use PDOStatement;
use stdClass;

trait InteractsWithDatabase
{
    // =========================================
    // REQUÊTES
    // =========================================

    /**
     * @param array<int|string, mixed> $params
     */
    protected function query(string $sql, array $params = []): PDOStatement|false
    {
        Profiler::increment('database.query.count');
        Profiler::start('database.query');

        $start = hrtime(true);

        try
        {
            $statement = $this->db->prepare($sql);

            if ($statement === false)
            {
                return false;
            }

            $statement->execute($params);

            return $statement;
        }
        finally
        {
            $duration = (hrtime(true) - $start) / 1_000_000;

            if (App::debug() && $duration >= DatabaseConfig::slowQueryThreshold())
            {
                Logger::warning(
                    'Requête SQL lente',
                    [
                        'duration_ms' => round($duration, 2),
                        'sql' => $sql,
                        'parameter_count' => count($params)
                    ]
                );
            }

            Profiler::end('database.query');
        }
    }

    /**
     * @template T of object
     *
     * @param array<int|string, mixed> $params
     * @param class-string<T>|null $class
     *
     * @return ($class is class-string<T> ? T|null : stdClass|null)
     */
    protected function fetchOne(
        string $sql,
        array $params = [],
        ?string $class = null
    ): ?object {
        $statement = $this->query($sql, $params);

        if ($statement === false)
        {
            return null;
        }

        if ($class !== null)
        {
            $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        }

        $result = $statement->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * @template T of object
     *
     * @param array<int|string, mixed> $params
     * @param class-string<T>|null $class
     *
     * @return ($class is class-string<T> ? list<T> : list<stdClass>)
     */
    protected function fetchAll(
        string $sql,
        array $params = [],
        ?string $class = null
    ): array {
        $statement = $this->query($sql, $params);

        if ($statement === false)
        {
            return [];
        }

        if ($class !== null)
        {
            $statement->setFetchMode(PDO::FETCH_CLASS, $class);

            /** @var list<T> $results */
            $results = $statement->fetchAll();

            return $results;
        }

        /** @var list<stdClass> $results */
        $results = $statement->fetchAll();

        return $results;
    }

    /**
     * @param array<int|string, mixed> $params
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $this->guardWrite();

        return $this->query($sql, $params) !== false;
    }

    // =========================================
    // ÉCRITURE
    // =========================================

    private function guardWrite(): void
    {
        if (App::isTesting())
        {
            throw new LogicException(
                'Écriture en base interdite pendant les tests.'
            );
        }
    }
}