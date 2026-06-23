<?php

declare(strict_types=1);

namespace App\Services\Stats;

use App\DTO\Home\Responses\DashboardStatsData;
use App\DTO\Manga\Responses\MangaStatsData;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use App\Repositories\Manga\ArtbookRepository;
use App\Repositories\Manga\MangaStatsRepository;

final readonly class StatsService
{
    public function __construct(
        private MangaStatsRepository $mangaStatsRepository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private ChinoisGrammaireRepository $grammaireRepository,
        private ArtbookRepository $artbookRepository
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | MANGA
    |--------------------------------------------------------------------------
    */

    public function totalTomes(): int
    {
        return $this->mangaStatsRepository->countAllTomes();
    }

    public function totalSeries(): int
    {
        return $this->mangaStatsRepository->countSeries();
    }

    public function totalRead(): int
    {
        return $this->mangaStatsRepository->countRead();
    }

    public function totalUnread(): int
    {
        return max(0, $this->totalTomes() - $this->totalRead());
    }

    public function readingProgress(): int
    {
        return $this->percentage($this->totalTomes(), $this->totalRead());
    }

    public function averageNote(): ?float
    {
        return $this->mangaStatsRepository->averageNote();
    }

    public function lastTome(): ?MangaStatsData
    {
        return $this->mangaStatsRepository->findLastAddedDto();
    }

    public function longestSeries(): ?MangaStatsData
    {
        return $this->mangaStatsRepository->findLongestSeriesDto();
    }

    /**
     * @return list<MangaStatsData>
     */
    public function topLongestSeries(int $limit = 5): array
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
        $totalArtbooks = $this->artbookRepository->countAll();
        $totalArtbookAuthors = $this->artbookRepository->countAuthors();
        $totalArtbookSeries = $this->artbookRepository->countSeries();

        $latestArtbook = $this->artbookRepository->findLatest();
        $mostRepresented = $this->artbookRepository->findMostRepresented();

        $totalTomes = $this->totalTomes();
        $totalSeries = $this->totalSeries();
        $totalRead = $this->totalRead();

        $totalVocabulary = $this->vocabulaireRepository->countAll();
        $remainingVocabulary = $this->vocabulaireRepository->countRemaining();

        $totalGrammar = $this->grammaireRepository->countAll();
        $remainingGrammar = $this->grammaireRepository->countRemaining();

        $vocabularyProgress = $this->progress($totalVocabulary, $remainingVocabulary);
        $grammarProgress = $this->progress($totalGrammar, $remainingGrammar);

        $totalChinese = $totalVocabulary + $totalGrammar;
        $totalRemainingChinese = $remainingVocabulary + $remainingGrammar;

        return new DashboardStatsData(
            totalVocabulary: $totalVocabulary,
            remainingVocabulary: $remainingVocabulary,
            vocabularyProgress: $vocabularyProgress,

            totalGrammar: $totalGrammar,
            remainingGrammar: $remainingGrammar,
            grammarProgress: $grammarProgress,

            globalChineseProgress: $this->progress($totalChinese, $totalRemainingChinese),

            totalTomes: $totalTomes,
            totalSeries: $totalSeries,

            totalRead: $totalRead,
            totalUnread: $this->totalUnread(),

            readingProgress: $this->readingProgress(),

            totalArtbooks: $totalArtbooks,
            totalArtbookAuthors: $totalArtbookAuthors,
            totalArtbookSeries: $totalArtbookSeries,

            latestArtbook: $latestArtbook,
            mostRepresented: $mostRepresented,

            averageNote: $this->averageNote(),

            lastTome: $this->lastTome(),
            longestSeries: $this->longestSeries(),

            topLongestSeries: $this->topLongestSeries(),

            lowRatedMangas: [],
            lowJacquetteMangas: [],
            lowLivreStateMangas: []
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function progress(int $total, int $remaining): int
    {
        if ($total <= 0)
        {
            return 0;
        }

        return (int) round((($total - $remaining) / $total) * 100);
    }

    private function percentage(int $total, int $value): int
    {
        if ($total <= 0)
        {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }
}
