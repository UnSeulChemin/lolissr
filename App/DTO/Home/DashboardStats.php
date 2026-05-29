<?php

declare(strict_types=1);

namespace App\DTO\Home;

use App\Models\Manga;

final readonly class DashboardStats
{
    /**
     * @param list<Manga> $topLongestSeries
     * @param list<Manga> $lowRatedMangas
     * @param list<Manga> $lowJacquetteMangas
     * @param list<Manga> $lowLivreStateMangas
     */
    public function __construct(
        public int $totalTomes,
        public int $totalSeries,
        public int $totalRead,
        public int $totalUnread,
        public int $readingProgress,
        public ?float $averageNote,
        public ?Manga $lastTome,
        public ?Manga $longestSeries,
        public array $topLongestSeries,
        public array $lowRatedMangas,
        public array $lowJacquetteMangas,
        public array $lowLivreStateMangas,
    ) {
    }
}
