<?php

declare(strict_types=1);

namespace App\DTO\Home;

final class DashboardStats
{
    public function __construct(
        public int $totalTomes,
        public int $totalSeries,
        public int $totalRead,
        public int $totalUnread,
        public int $readingProgress,
        public ?float $averageNote,
        public ?object $lastTome,
        public ?object $longestSeries,
        public array $topLongestSeries,
        public array $lowRatedMangas,
        public array $lowJacquetteMangas,
        public array $lowLivreStateMangas,
    ) {}
}