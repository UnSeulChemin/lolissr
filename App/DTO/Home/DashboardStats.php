<?php

declare(strict_types=1);

namespace App\DTO\Home;

use App\DTO\Manga\Responses\MangaStatsData;

final readonly class DashboardStats
{
    /**
     * @param list<MangaStatsData> $topLongestSeries
     * @param list<MangaStatsData> $lowRatedMangas
     * @param list<MangaStatsData> $lowJacquetteMangas
     * @param list<MangaStatsData> $lowLivreStateMangas
     */
    public function __construct(
        public int $totalVocabulary,
        public int $remainingVocabulary,
        public int $vocabularyProgress,

        public int $totalGrammar,
        public int $remainingGrammar,
        public int $grammarProgress,

        public int $globalChineseProgress,

        public int $totalTomes,
        public int $totalSeries,

        public int $totalRead,
        public int $totalUnread,
        public int $readingProgress,

        public ?float $averageNote,

        public ?MangaStatsData $lastTome,
        public ?MangaStatsData $longestSeries,

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