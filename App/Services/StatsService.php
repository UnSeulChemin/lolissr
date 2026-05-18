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
        $value = $this->repository->averageNote();

        return $value !== null ? (float) $value : null;
    }

    /**
     * 🔥 SAFE CACHE FRIENDLY
     */
    public function lastTome(): ?array
    {
        $data = $this->repository->findLastAdded();

        return $data ? (array) $data : null;
    }

    /**
     * 🔥 SAFE CACHE FRIENDLY
     */
    public function longestSeries(): ?array
    {
        $data = $this->repository->findLongestSeries();

        return $data ? (array) $data : null;
    }

    public function topLongestSeries(int $limit = 5): array
    {
        return $this->normalizeList(
            $this->repository->topLongestSeries($limit)
        );
    }

    public function lowRated(int $limit = 5): array
    {
        return $this->normalizeList(
            $this->repository->findLowRatedMangas($limit)
        );
    }

    public function lowJacquette(int $limit = 5): array
    {
        return $this->normalizeList(
            $this->repository->findLowJacquetteMangas($limit)
        );
    }

    public function lowLivreState(int $limit = 5): array
    {
        return $this->normalizeList(
            $this->repository->findLowLivreStateMangas($limit)
        );
    }

    /**
     * 🔥 DASHBOARD SAFE POUR CACHE
     */
    public function dashboard(): array
    {
        $totalTomes = $this->totalTomes();
        $totalRead = $this->totalRead();

        return [
            'totalTomes' => $totalTomes,
            'totalSeries' => $this->totalSeries(),
            'totalRead' => $totalRead,
            'totalUnread' => max(0, $totalTomes - $totalRead),
            'readingProgress' => $this->readingProgress(),
            'averageNote' => $this->averageNote(),

            // SAFE ARRAY ONLY
            'lastTome' => $this->lastTome(),
            'longestSeries' => $this->longestSeries(),

            // LISTS SAFE
            'topLongestSeries' => $this->topLongestSeries(),
            'lowRatedMangas' => $this->lowRated(),
            'lowJacquetteMangas' => $this->lowJacquette(),
            'lowLivreStateMangas' => $this->lowLivreState(),
        ];
    }

    /**
     * 🔥 NORMALISE TOUT EN ARRAY SAFE POUR CACHE
     */
    private function normalizeList(array $items): array
    {
        return array_map(
            static function ($item) {
                if (is_object($item)) {
                    return (array) $item;
                }

                return $item;
            },
            $items
        );
    }
}