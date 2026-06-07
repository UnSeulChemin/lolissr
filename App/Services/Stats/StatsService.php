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
        $totalTomes =
            $this->totalTomes();

        if ($totalTomes <= 0)
        {
            return 0;
        }

        return (int) round(
            (
                $this->totalRead()
                / $totalTomes
            ) * 100,
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

        $totalVocabulary =
            $this->vocabulaireRepository
                ->countAll();

        $remainingVocabulary =
            $this->vocabulaireRepository
                ->countRemaining();

        $vocabularyProgress =
            $totalVocabulary > 0
                ? (int) round(
                    (
                        ($totalVocabulary - $remainingVocabulary)
                        / $totalVocabulary
                    ) * 100,
                )
                : 0;

        $totalGrammar =
            $this->grammaireRepository
                ->countAll();

        $remainingGrammar =
            $this->grammaireRepository
                ->countRemaining();

        $grammarProgress =
            $totalGrammar > 0
                ? (int) round(
                    (
                        ($totalGrammar - $remainingGrammar)
                        / $totalGrammar
                    ) * 100,
                )
                : 0;

        $totalChinese =
            $totalVocabulary
            + $totalGrammar;

        $totalRemainingChinese =
            $remainingVocabulary
            + $remainingGrammar;

        $globalChineseProgress =
            $totalChinese > 0
                ? (int) round(
                    (
                        ($totalChinese - $totalRemainingChinese)
                        / $totalChinese
                    ) * 100,
                )
                : 0;

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
            totalSeries: $this->totalSeries(),

            totalRead: $totalRead,
            totalUnread: max(
                0,
                $totalTomes - $totalRead,
            ),

            readingProgress: $totalTomes > 0
                ? (int) round(
                    (
                        $totalRead
                        / $totalTomes
                    ) * 100,
                )
                : 0,

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
}