<?php

declare(strict_types=1);

namespace App\Services\Sql;

use App\Repositories\Sql\SqlRepository;

final readonly class SqlReadService
{
    public function __construct(
        private SqlRepository $sqlRepository
    ) {
    }

    /**
     * @return list<object>
     */
    public function execute(string $sql): array
    {
        return $this->sqlRepository->executeQuery(trim($sql));
    }
}
