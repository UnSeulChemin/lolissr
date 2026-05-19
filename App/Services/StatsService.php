<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Manga\MangaRepository;

final class StatsService
{
    public function __construct(
        private readonly MangaRepository $repository
    ) {}

    public function totalTomes(): int
    {
        return (int) $this->repository->countAllTomes();
    }

    public function totalSeries(): int
    {
        return (int) $this->repository->countSeries();
    }

    public function totalRead(): int
    {
        return (int) $this->repository->countRead();
    }

    public function totalUnread(): int
    {
        return max(
            0,
            $this->totalTomes() - $this->totalRead()
        );
    }

    public function readingProgress(): int
    {
        $total = $this->totalTomes();

        return $total > 0
            ? (int) round(
                ($this->totalRead() / $total) * 100
            )
            : 0;
    }

    public function averageNote(): ?float
    {
        $value = $this->repository->averageNote();

        return $value !== null
            ? (float) $value
            : null;
    }

    public function lastTome(): ?object
    {
        return $this->repository->findLastAdded();
    }

    public function longestSeries(): ?object
    {
        return $this->repository->findLongestSeries();
    }

    public function topLongestSeries(
        int $limit = 5
    ): array {
        return $this->repository
            ->topLongestSeries($limit);
    }

    public function dashboard(): object
    {
        return (object) [
            'totalTomes' =>
                $this->totalTomes(),

            'totalSeries' =>
                $this->totalSeries(),

            'totalRead' =>
                $this->totalRead(),

            'totalUnread' =>
                $this->totalUnread(),

            'readingProgress' =>
                $this->readingProgress(),

            'averageNote' =>
                $this->averageNote(),

            'lastTome' =>
                $this->lastTome(),

            'longestSeries' =>
                $this->longestSeries(),

            'topLongestSeries' =>
                $this->topLongestSeries(),
        ];
    }
}