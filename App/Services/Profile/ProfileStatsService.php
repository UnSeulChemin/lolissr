<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\UserXp;
use App\DTO\Profile\ProfileStatsData;
use App\Repositories\Manga\MangaStatsRepository;
use App\Repositories\Chinois\ChinoisVocabulaireStatsRepository;
use App\Repositories\Chinois\ChinoisGrammaireStatsRepository;

final readonly class ProfileStatsService
{
    public function __construct(
        private MangaStatsRepository $mangaStatsRepository,
        private ChinoisVocabulaireStatsRepository $vocabularyStatsRepository,
        private ChinoisGrammaireStatsRepository $grammarStatsRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | MANGA
    |--------------------------------------------------------------------------
    */

    public function readTomes(): int
    {
        return $this->mangaStatsRepository->countRead();
    }

    public function completedSeries(): int
    {
        return $this->mangaStatsRepository->countCompletedSeries();
    }

    /*
    |--------------------------------------------------------------------------
    | CHINESE
    |--------------------------------------------------------------------------
    */

    public function learnedVocabulary(): int
    {
        return $this->vocabularyStatsRepository->countMastered();
    }

    public function learnedGrammar(): int
    {
        return $this->grammarStatsRepository->countMastered();
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    public function getStats(): ProfileStatsData
    {
        // MANGA
        $readTomes = $this->readTomes();
        $completedSeries = $this->completedSeries();

        // MANGA XP
        $tomeXp = $readTomes * UserXp::READ_TOME;
        $seriesXp = $completedSeries * UserXp::COMPLETE_SERIES;

        // CHINESE
        $vocabularyLearned = $this->learnedVocabulary();
        $grammarLearned = $this->learnedGrammar();

        // CHINESE XP
        $vocabularyXp = $vocabularyLearned * UserXp::LEARN_VOCABULARY;
        $grammarXp = $grammarLearned * UserXp::LEARN_GRAMMAR;

        return new ProfileStatsData(
            readTomes: $readTomes,
            completedSeries: $completedSeries,

            tomeXp: $tomeXp,
            seriesXp: $seriesXp,

            vocabularyLearned: $vocabularyLearned,
            grammarLearned: $grammarLearned,

            vocabularyXp: $vocabularyXp,
            grammarXp: $grammarXp,

            totalXp: $tomeXp + $seriesXp + $vocabularyXp + $grammarXp,
        );
    }
}
