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
        return $this->repository->countAllTomes();
    }

    public function totalSeries(): int
    {
        return $this->repository->countSeries();
    }

    public function totalRead(): int
    {
        return $this->repository->countRead();
    }

    public function totalUnread(): int
    {
        return max(0, $this->totalTomes() - $this->totalRead());
    }

    public function readingProgress(): int
    {
        $totalTomes = $this->totalTomes();

        if ($totalTomes <= 0) {
            return 0;
        }

        return (int) round(($this->totalRead() / $totalTomes) * 100);
    }

    public function averageNote(): ?float
    {
        return $this->repository->averageNote();
    }

    public function lastTome(): object|false
    {
        return $this->repository->findLastAdded();
    }

    public function longestSeries(): object|false
    {
        return $this->repository->findLongestSeries();
    }

    public function topLongestSeries(int $limit = 5): array
    {
        return $this->repository->topLongestSeries($limit);
    }

    public function lowRated(int $limit = 5): array
    {
        return $this->repository->findLowRatedMangas($limit);
    }

    public function lowJacquette(int $limit = 5): array
    {
        return $this->repository->findLowJacquetteMangas($limit);
    }

    public function lowLivreState(int $limit = 5): array
    {
        return $this->repository->findLowLivreStateMangas($limit);
    }

    public function dashboard(): array
    {
        $totalTomes = $this->totalTomes();
        $totalRead = $this->totalRead();
        $totalUnread = max(0, $totalTomes - $totalRead);

        $readingProgress = $totalTomes > 0
            ? (int) round(($totalRead / $totalTomes) * 100)
            : 0;

        return [
            'totalTomes' => $totalTomes,
            'totalSeries' => $this->totalSeries(),
            'totalRead' => $totalRead,
            'totalUnread' => $totalUnread,
            'readingProgress' => $readingProgress,
            'averageNote' => $this->averageNote(),
            'lastTome' => $this->lastTome(),
            'longestSeries' => $this->longestSeries(),
            'topLongestSeries' => $this->topLongestSeries(),
            'lowRatedMangas' => $this->lowRated(),
            'lowJacquetteMangas' => $this->lowJacquette(),
            'lowLivreStateMangas' => $this->lowLivreState(),
        ];
    }
}