<?php

declare(strict_types=1);

namespace App\DTO\Home\Responses;

use App\DTO\Manga\Responses\ArtbookStatsData;
use App\DTO\Manga\Responses\ArtbookRepresentationData;
use App\DTO\Manga\Responses\MangaStatsData;

final readonly class DashboardStatsData
{
    /**
     * @param list<MangaStatsData> $topLongestSeries
     * @param list<MangaStatsData> $lowRatedMangas
     * @param list<MangaStatsData> $lowJacquetteMangas
     * @param list<MangaStatsData> $lowLivreStateMangas
     */
    public function __construct(
        // Chinois
        public int $totalVocabulary,
        public int $remainingVocabulary,
        public int $learnedVocabulary,
        public int $vocabularyProgress,

        public int $totalGrammar,
        public int $remainingGrammar,
        public int $learnedGrammar,
        public int $grammarProgress,

        public int $globalChineseProgress,
        public string $globalChineseProgressLabel,

        // Manga
        public int $totalTomes,
        public int $totalSeries,

        public int $totalRead,
        public int $totalUnread,
        public int $readingProgress,

        public ?float $averageNote,
        public string $averageNoteLabel,

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

        // Artbooks
        public int $totalArtbooks,
        public int $totalArtbookAuthors,
        public int $totalArtbookSeries,

        public ?ArtbookStatsData $latestArtbook,
        public ?ArtbookRepresentationData $mostRepresented,
    ) {
    }
}