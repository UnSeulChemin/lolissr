<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\UserXp;
use App\DTO\Profile\ProfileStatsData;
use App\Repositories\Chinois\ChinoisGrammaireStatsRepository;
use App\Repositories\Chinois\ChinoisVocabulaireStatsRepository;
use App\Repositories\Figurine\FigurineStatsRepository;
use App\Repositories\Manga\ArtbookStatsRepository;
use App\Repositories\Manga\MangaStatsRepository;
use App\Repositories\Nendoroid\NendoroidStatsRepository;

final readonly class ProfileStatsService
{
    public function __construct(
        private MangaStatsRepository $mangaStatsRepository,
        private ArtbookStatsRepository $artbookStatsRepository,
        private FigurineStatsRepository $figurineStatsRepository,
        private NendoroidStatsRepository $nendoroidStatsRepository,
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
    | ARTBOOKS
    |--------------------------------------------------------------------------
    */

    public function readArtbooks(): int
    {
        return $this->artbookStatsRepository->countRead();
    }

    /*
    |--------------------------------------------------------------------------
    | FIGURINES
    |--------------------------------------------------------------------------
    */

    public function collectedFigurines(): int
    {
        return $this->figurineStatsRepository->countCollected();
    }

    /*
    |--------------------------------------------------------------------------
    | NENDOROIDS
    |--------------------------------------------------------------------------
    */

    public function collectedNendoroids(): int
    {
        return $this->nendoroidStatsRepository->countCollected();
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

        // ARTBOOKS
        $readArtbooks = $this->readArtbooks();

        // ARTBOOKS XP
        $artbookXp = $readArtbooks * UserXp::READ_ARTBOOK;

        // FIGURINES
        $figurinesCollected = $this->collectedFigurines();

        // FIGURINES XP
        $figurinesXp = $figurinesCollected * UserXp::COLLECT_FIGURINE;

        // NENDOROIDS
        $nendoroidsCollected = $this->collectedNendoroids();

        // NENDOROIDS XP
        $nendoroidsXp = $nendoroidsCollected * UserXp::COLLECT_NENDOROID;

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

            readArtbooks: $readArtbooks,
            artbookXp: $artbookXp,

            figurinesCollected: $figurinesCollected,
            figurinesXp: $figurinesXp,

            nendoroidsCollected: $nendoroidsCollected,
            nendoroidsXp: $nendoroidsXp,

            vocabularyLearned: $vocabularyLearned,
            grammarLearned: $grammarLearned,

            vocabularyXp: $vocabularyXp,
            grammarXp: $grammarXp,

            totalXp:
                $tomeXp
                + $seriesXp
                + $artbookXp
                + $figurinesXp
                + $nendoroidsXp
                + $vocabularyXp
                + $grammarXp,
        );
    }
}