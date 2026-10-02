<?php

declare(strict_types=1);

namespace App\DTO\Profile\Responses;

final readonly class ProfileUnlockStatsData
{
    public function __construct(
        public int $readTomes = 0,
        public int $completedSeries = 0,
        public int $readArtbooks = 0,
        public int $figurinesCollected = 0,
        public int $nendoroidsCollected = 0,
        public int $peluchesCollected = 0,
        public int $vocabularyLearned = 0,
        public int $grammarLearned = 0,
    ) {
    }
}
