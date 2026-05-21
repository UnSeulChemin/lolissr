<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Home\DashboardStats;
use App\Models\Manga;
use App\Repositories\Manga\MangaStatsRepository;

final readonly class StatsService
{
    public function __construct(
        private MangaStatsRepository $repository,
    ) {
    }

    public function totalTomes(): int
    {
        return $this->repository
            ->countAllTomes();
    }

    public function totalSeries(): int
    {
        return $this->repository
            ->countSeries();
    }

    public function totalRead(): int
    {
        return $this->repository
            ->countRead();
    }

    public function totalUnread(): int
    {
        return max(
            0,
            $this->totalTomes()
            - $this->totalRead(),
        );
    }

    public function readingProgress(): int
    {
        $totalTomes = $this->totalTomes();

        if ($totalTomes <= 0) {
            return 0;
        }

        return (int) round(
            (
                $this->totalRead()
                / $totalTomes
            ) * 100,
        );
    }

    public function averageNote(): ?float
    {
        return $this->repository
            ->averageNote();
    }

    public function lastTome(): ?Manga
    {
        return $this->repository
            ->findLastAdded();
    }

    public function longestSeries(): ?Manga
    {
        return $this->repository
            ->findLongestSeries();
    }

    /**
     * @return list<Manga>
     */
    public function topLongestSeries(
        int $limit = 5,
    ): array {
        return $this->repository
            ->topLongestSeries($limit);
    }

    public function dashboard(): DashboardStats
    {
        $totalTomes = $this->totalTomes();

        $totalRead = $this->totalRead();

        return new DashboardStats(
            totalTomes: $totalTomes,

            totalSeries: $this->totalSeries(),

            totalRead: $totalRead,

            totalUnread: max(
                0,
                $totalTomes - $totalRead,
            ),

            readingProgress: $totalTomes > 0
                ? (int) round(
                    ($totalRead / $totalTomes)
                    * 100,
                )
                : 0,

            averageNote: $this->averageNote(),

            lastTome: $this->lastTome(),

            longestSeries: $this->longestSeries(),

            topLongestSeries: $this->topLongestSeries(),

            lowRatedMangas: [],

            lowJacquetteMangas: [],

            lowLivreStateMangas: [],
        );
    }
}