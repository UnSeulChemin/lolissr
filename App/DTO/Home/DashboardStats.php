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

        // =========================================
        // CHINOIS
        // =========================================

        public int $totalVocabulary,
        public int $remainingVocabulary,
        public int $vocabularyProgress,

        public int $totalGrammar,
        public int $remainingGrammar,
        public int $grammarProgress,

        // =========================================
        // MANGA
        // =========================================

        public int $totalTomes,
        public int $totalSeries,

        public int $totalRead,
        public int $totalUnread,
        public int $readingProgress,

        public ?float $averageNote,

        public ?Manga $lastTome,
        public ?Manga $longestSeries,

        /** @var list<Manga> */
        public array $topLongestSeries,

        /** @var list<Manga> */
        public array $lowRatedMangas,

        /** @var list<Manga> */
        public array $lowJacquetteMangas,

        /** @var list<Manga> */
        public array $lowLivreStateMangas,
    ) {
    }
}