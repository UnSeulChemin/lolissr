<?php

declare(strict_types=1);

namespace App\Cache;

use App\DTO\Home\Responses\DashboardStatsData;
use App\Services\Stats\StatsService;

use Framework\Cache\Cache;

final readonly class DashboardCache
{
    public function __construct(
        private StatsService $statsService
    ) {
    }

    // =========================================
    // CACHE
    // =========================================

    public function get(): DashboardStatsData
    {
        /** @var array<string, mixed> $data */
        $data = Cache::remember(
            CacheKey::HOME_DASHBOARD,
            null,
            fn (): array => $this->statsService->dashboard()->toArray()
        );

        return DashboardStatsData::fromArray($data);
    }

    public function forget(): void
    {
        Cache::forget(CacheKey::HOME_DASHBOARD);
    }
}