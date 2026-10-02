<?php

declare(strict_types=1);

namespace App\Services\Sql;

use App\Repositories\Sql\SqlRepository;
use App\Cache\CacheKey;
use Framework\Cache\Cache;

final readonly class SqlExecutionService
{
    public function __construct(
        private SqlRepository $sqlRepository
    ) {
    }

    /**
     * @return array{result: list<object>, truncated: bool, limit: int}
     */
    public function execute(string $sql): array
    {
        $result = $this->sqlRepository->executeQuery($sql);

        // The console accepts arbitrary SQL: invalidate after every successful
        // execution instead of trying to classify writes with a SQL regex.
        Cache::forget(CacheKey::HOME_DASHBOARD);

        return $result;
    }
}
