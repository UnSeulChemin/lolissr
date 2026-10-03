<?php

declare(strict_types=1);

namespace App\Services\Sql;

use App\Cache\CacheKey;
use App\Repositories\Sql\SqlRepository;

use Framework\Cache\Cache;

final readonly class SqlExecutionService
{
    public function __construct(private SqlRepository $sqlRepository)
    {
    }

    /**
     * @return array{result: list<object>, truncated: bool, limit: int}
     */
    public function execute(string $sql): array
    {
        $result = $this->sqlRepository->executeQuery($sql);

        // La console accepte tout SQL : invalider après chaque exécution réussie
        // sans tenter de classer les écritures avec une expression régulière SQL.
        Cache::forget(CacheKey::HOME_DASHBOARD);

        return $result;
    }
}
