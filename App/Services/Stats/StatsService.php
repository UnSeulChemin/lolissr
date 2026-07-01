<?php

declare(strict_types=1);

namespace App\Services\Stats;

use App\DTO\Home\Responses\DashboardStatsData;
use App\DTO\Manga\Responses\MangaStatsData;
use App\Repositories\Manga\MangaStatsRepository;
use App\Repositories\Manga\ArtbookStatsRepository;
use App\Repositories\Chinois\ChinoisGrammaireStatsRepository;
use App\Repositories\Chinois\ChinoisVocabulaireStatsRepository;

final readonly class StatsService
{
    public function __construct(
        private MangaStatsRepository $mangaStatsRepository,
        private ArtbookStatsRepository $artbookStatsRepository,
        private ChinoisVocabulaireStatsRepository $vocabulaireStatsRepository,
        private ChinoisGrammaireStatsRepository $grammaireStatsRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | MANGA
    |--------------------------------------------------------------------------
    */

    public function totalMangaTomes(): int
    {
        return $this->mangaStatsRepository->countAllTomes();
    }

    public function totalMangaSeries(): int
    {
        return $this->mangaStatsRepository->countSeries();
    }

    public function totalMangaRead(): int
    {
        return $this->mangaStatsRepository->countRead();
    }

    public function averageMangaNote(): ?float
    {
        return $this->mangaStatsRepository->averageNote();
    }

    public function lastMangaTome(): ?MangaStatsData
    {
        return $this->mangaStatsRepository->findLastAddedDto();
    }

    public function longestMangaSeries(): ?MangaStatsData
    {
        return $this->mangaStatsRepository->findLongestSeriesDto();
    }

    /**
     * @return list<MangaStatsData>
     */
    public function topLongestMangaSeries(int $limit = 5): array
    {
        return $this->mangaStatsRepository->topLongestSeriesDto($limit);
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function dashboard(): DashboardStatsData
    {
        // MANGA
        $totalMangaTomes = $this->totalMangaTomes();
        $totalMangaSeries = $this->totalMangaSeries();
        $totalMangaRead = $this->totalMangaRead();
        $totalMangaUnread = max(0, $totalMangaTomes - $totalMangaRead);
        $mangaReadingProgress = $this->readingPercentage($totalMangaTomes, $totalMangaRead);
        $averageMangaNote = $this->averageMangaNote();
        $lastMangaTome = $this->lastMangaTome();
        $longestMangaSeries = $this->longestMangaSeries();
        $topLongestMangaSeries = $this->topLongestMangaSeries();

        // ARTBOOK
        $totalArtbooks = $this->artbookStatsRepository->countAll();
        $totalArtbookAuthors = $this->artbookStatsRepository->countAuthors();
        $totalArtbookSeries = $this->artbookStatsRepository->countSeries();
        $latestArtbook = $this->artbookStatsRepository->findLatest();
        $mostRepresentedArtbook = $this->artbookStatsRepository->findMostRepresented();

        // CHINOIS
        $totalVocabulary = $this->vocabulaireStatsRepository->countAll();
        $remainingVocabulary = $this->vocabulaireStatsRepository->countRemaining();
        $vocabularyProgress = $this->completionPercentage($totalVocabulary, $remainingVocabulary);
        $totalGrammar = $this->grammaireStatsRepository->countAll();
        $remainingGrammar = $this->grammaireStatsRepository->countRemaining();
        $grammarProgress = $this->completionPercentage($totalGrammar, $remainingGrammar);
        $totalChinese = $totalVocabulary + $totalGrammar;
        $totalRemainingChinese = $remainingVocabulary + $remainingGrammar;
        $globalChineseProgress = $this->completionPercentage($totalChinese, $totalRemainingChinese);

        return new DashboardStatsData(
            totalTomes: $totalMangaTomes,
            totalSeries: $totalMangaSeries,
            totalRead: $totalMangaRead,
            totalUnread: $totalMangaUnread,
            readingProgress: $mangaReadingProgress,

            averageNote: $averageMangaNote,
            lastTome: $lastMangaTome,
            longestSeries: $longestMangaSeries,
            topLongestSeries: $topLongestMangaSeries,

            lowRatedMangas: [],
            lowJacquetteMangas: [],
            lowLivreStateMangas: [],

            totalArtbooks: $totalArtbooks,
            totalArtbookAuthors: $totalArtbookAuthors,
            totalArtbookSeries: $totalArtbookSeries,
            latestArtbook: $latestArtbook,
            mostRepresented: $mostRepresentedArtbook,

            totalVocabulary: $totalVocabulary,
            remainingVocabulary: $remainingVocabulary,
            vocabularyProgress: $vocabularyProgress,

            totalGrammar: $totalGrammar,
            remainingGrammar: $remainingGrammar,
            grammarProgress: $grammarProgress,

            globalChineseProgress: $globalChineseProgress,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function completionPercentage(int $total, int $remaining): int
    {
        if ($total <= 0)
        {
            return 0;
        }

        return (int) round((($total - $remaining) / $total) * 100);
    }

    private function readingPercentage(int $total, int $read): int
    {
        if ($total <= 0)
        {
            return 0;
        }

        return (int) round(($read / $total) * 100);
    }
}
