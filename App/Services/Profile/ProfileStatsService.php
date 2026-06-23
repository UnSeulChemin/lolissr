<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\UserXp;
use App\DTO\Profile\ProfileStatsData;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use App\Repositories\Manga\MangaStatsRepository;

final readonly class ProfileStatsService
{
    public function __construct(
        private MangaStatsRepository $mangaStatsRepository,
        private ChinoisVocabulaireRepository $vocabularyRepository,
        private ChinoisGrammaireRepository $grammarRepository
    ) {
    }

    public function getStats(): ProfileStatsData
    {
        $readTomes = $this->mangaStatsRepository->countRead();
        $completedSeries = $this->mangaStatsRepository->countCompletedSeries();

        $vocabularyLearned = $this->vocabularyRepository->countMastered();
        $grammarLearned = $this->grammarRepository->countMastered();

        $tomeXp = $readTomes * UserXp::READ_TOME;
        $seriesXp = $completedSeries * UserXp::COMPLETE_SERIES;

        $vocabularyXp = $vocabularyLearned * UserXp::LEARN_VOCABULARY;
        $grammarXp = $grammarLearned * UserXp::LEARN_GRAMMAR;

        return new ProfileStatsData(
            readTomes: $readTomes,
            tomeXp: $tomeXp,

            completedSeries: $completedSeries,
            seriesXp: $seriesXp,

            vocabularyLearned: $vocabularyLearned,
            vocabularyXp: $vocabularyXp,

            grammarLearned: $grammarLearned,
            grammarXp: $grammarXp,

            totalXp: $tomeXp + $seriesXp + $vocabularyXp + $grammarXp
        );
    }
}
