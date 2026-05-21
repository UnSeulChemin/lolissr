<?php

declare(strict_types=1);

namespace App\DTO\Home;

use App\DTO\Manga\MangaDTO;
use App\DTO\Manga\TomeDTO;

final readonly class DashboardStats
{
    /**
     * @param list<MangaDTO> $topLongestSeries
     * @param list<MangaDTO> $lowRatedMangas
     * @param list<MangaDTO> $lowJacquetteMangas
     * @param list<MangaDTO> $lowLivreStateMangas
     */
    public function __construct(
        public int $totalTomes,
        public int $totalSeries,
        public int $totalRead,
        public int $totalUnread,
        public int $readingProgress,
        public ?float $averageNote,
        public ?TomeDTO $lastTome,
        public ?MangaDTO $longestSeries,
        public array $topLongestSeries,
        public array $lowRatedMangas,
        public array $lowJacquetteMangas,
        public array $lowLivreStateMangas,
    ) {
    }
}