<?php

declare(strict_types=1);

namespace App\Services\Stats;

use App\DTO\Home\DashboardStats;
use App\Models\Manga;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use App\Repositories\Manga\MangaStatsRepository;

final readonly class StatsService
{
    public function __construct(
        private MangaStatsRepository $repository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private ChinoisGrammaireRepository $grammaireRepository,
    ) {
    }

    public function totalTomes(): int
    {
        return $this->repository
            ->countAllTomes();
    }

    public function totalSeries(): int
    {
        return $this->repository
            ->countSeries();
    }

    public function totalRead(): int
    {
        return $this->repository
            ->countRead();
    }

    public function totalUnread(): int
    {
        return max(
            0,
            $this->totalTomes()
            - $this->totalRead(),
        );
    }

    public function readingProgress(): int
    {
        return $this->percentage(
            $this->totalTomes(),
            $this->totalRead(),
        );
    }

    public function averageNote(): ?float
    {
        return $this->repository
            ->averageNote();
    }

    public function lastTome(): ?Manga
    {
        return $this->repository
            ->findLastAdded();
    }

    public function longestSeries(): ?Manga
    {
        return $this->repository
            ->findLongestSeries();
    }

    /**
     * @return list<Manga>
     */
    public function topLongestSeries(
        int $limit = 5,
    ): array {
        return $this->repository
            ->topLongestSeries($limit);
    }

    public function dashboard(): DashboardStats
    {
        $totalTomes =
            $this->totalTomes();

        $totalRead =
            $this->totalRead();

        $totalSeries =
            $this->totalSeries();

        $totalUnread =
            max(
                0,
                $totalTomes - $totalRead,
            );

        $readingProgress =
            $this->percentage(
                $totalTomes,
                $totalRead,
            );

        $totalVocabulary =
            $this->vocabulaireRepository
                ->countAll();

        $remainingVocabulary =
            $this->vocabulaireRepository
                ->countRemaining();

        $vocabularyProgress =
            $this->progress(
                $totalVocabulary,
                $remainingVocabulary,
            );

        $totalGrammar =
            $this->grammaireRepository
                ->countAll();

        $remainingGrammar =
            $this->grammaireRepository
                ->countRemaining();

        $grammarProgress =
            $this->progress(
                $totalGrammar,
                $remainingGrammar,
            );

        $totalChinese =
            $totalVocabulary
            + $totalGrammar;

        $totalRemainingChinese =
            $remainingVocabulary
            + $remainingGrammar;

        $globalChineseProgress =
            $this->progress(
                $totalChinese,
                $totalRemainingChinese,
            );

        return new DashboardStats(
            totalVocabulary: $totalVocabulary,
            remainingVocabulary: $remainingVocabulary,
            vocabularyProgress: $vocabularyProgress,

            totalGrammar: $totalGrammar,
            remainingGrammar: $remainingGrammar,
            grammarProgress: $grammarProgress,

            globalChineseProgress:
                $globalChineseProgress,

            totalTomes: $totalTomes,
            totalSeries: $totalSeries,

            totalRead: $totalRead,
            totalUnread: $totalUnread,

            readingProgress: $readingProgress,

            averageNote: $this->averageNote(),

            lastTome: $this->lastTome(),
            longestSeries: $this->longestSeries(),

            topLongestSeries:
                $this->topLongestSeries(),

            lowRatedMangas: [],
            lowJacquetteMangas: [],
            lowLivreStateMangas: [],
        );
    }

    private function progress(
        int $total,
        int $remaining,
    ): int {

        if ($total <= 0)
        {
            return 0;
        }

        return (int) round(
            (
                ($total - $remaining)
                / $total
            ) * 100,
        );
    }

    private function percentage(
        int $total,
        int $value,
    ): int {

        if ($total <= 0)
        {
            return 0;
        }

        return (int) round(
            ($value / $total) * 100,
        );
    }
}