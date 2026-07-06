<?php

declare(strict_types=1);

namespace App\DTO\Profile;

final readonly class ProfileStatsData
{
    public function __construct(
        public int $readTomes,
        public int $tomeXp,
        public int $completedSeries,
        public int $seriesXp,
        public int $figurinesCollected,
        public int $figurinesXp,
        public int $vocabularyLearned,
        public int $vocabularyXp,
        public int $grammarLearned,
        public int $grammarXp,
        public int $totalXp,
    ) {
    }
}