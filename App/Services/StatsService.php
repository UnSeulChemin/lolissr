<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Manga\MangaStatsRepository;
use stdClass;

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
        $totalTomes = $this->totalTomes();
        $totalRead = $this->totalRead();

        return max(
            0,
            $totalTomes - $totalRead,
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

    public function lastTome(): ?object
    {
        return $this->repository
            ->findLastAdded();
    }

    public function longestSeries(): ?object
    {
        return $this->repository
            ->findLongestSeries();
    }

    /**
     * @return list<object>
     */
    public function topLongestSeries(
        int $limit = 5,
    ): array {
        return $this->repository
            ->topLongestSeries($limit);
    }

    public function dashboard(): object
    {
        $totalTomes = $this->totalTomes();

        $totalRead = $this->totalRead();

        return (object) [
            'totalTomes' => $totalTomes,

            'totalSeries' => $this->totalSeries(),

            'totalRead' => $totalRead,

            'totalUnread' => max(
                0,
                $totalTomes - $totalRead,
            ),

            'readingProgress' => $totalTomes > 0
                ? (int) round(
                    ($totalRead / $totalTomes)
                    * 100,
                )
                : 0,

            'averageNote' => $this->averageNote(),

            'lastTome' => $this->lastTome(),

            'longestSeries' => $this->longestSeries(),

            'topLongestSeries' => $this->topLongestSeries(),
        ];
    }
}