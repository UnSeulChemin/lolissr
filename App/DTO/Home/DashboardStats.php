<?php

declare(strict_types=1);

namespace App\DTO\Home;

use App\DTO\Manga\Responses\MangaStatsData;
use App\Models\Artbook;

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

        public int $totalArtbooks,
        public int $totalArtbookAuthors,
        public int $totalArtbookSeries,

        public ?Artbook $latestArtbook,

        public ?float $averageNote,

        public ?MangaStatsData $lastTome,
        public ?MangaStatsData $longestSeries,

        /** @var list<MangaStatsData> */
        public array $topLongestSeries,

        /** @var list<MangaStatsData> */
        public array $lowRatedMangas,

        /** @var list<MangaStatsData> */
        public array $lowJacquetteMangas,

        /** @var list<MangaStatsData> */
        public array $lowLivreStateMangas,
    ) {
    }
}