<?php

declare(strict_types=1);

namespace App\Cache\Home;

use App\Cache\CacheKey;
use App\DTO\Home\Responses\DashboardStatsData;
use App\Services\Home\DashboardStatsService;

use Framework\Cache\Cache;

final readonly class DashboardCache
{
    public function __construct(private DashboardStatsService $dashboardStatsService)
    {
    }

    // =================================================
    // CACHE
    // =================================================

    public function get(): DashboardStatsData
    {
        /** @var array<string, mixed> $data */
        $data = Cache::remember(
            CacheKey::dashboard(),
            null,
            fn (): array => $this->dashboardStatsService->dashboard()->toArray()
        );

        return DashboardStatsData::fromArray($data);
    }

    public function forget(): void
    {
        Cache::forget(CacheKey::dashboard());
    }
}
