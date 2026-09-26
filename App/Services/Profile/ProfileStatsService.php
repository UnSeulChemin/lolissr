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
use App\Repositories\Peluche\PelucheStatsRepository;

final readonly class ProfileStatsService
{
    public function __construct(
        private MangaStatsRepository $mangaStatsRepository,
        private ArtbookStatsRepository $artbookStatsRepository,
        private FigurineStatsRepository $figurineStatsRepository,
        private NendoroidStatsRepository $nendoroidStatsRepository,
        private PelucheStatsRepository $pelucheStatsRepository,
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
    | PELUCHES
    |--------------------------------------------------------------------------
    */

    public function collectedPeluches(): int
    {
        return $this->pelucheStatsRepository->countCollected();
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
        $manga = $this->mangaStatsRepository->profileSummary();
        $artbook = $this->artbookStatsRepository->profileSummary();
        $figurine = $this->figurineStatsRepository->profileSummary();
        $nendoroid = $this->nendoroidStatsRepository->profileSummary();
        $peluche = $this->pelucheStatsRepository->profileSummary();
        $vocabulary = $this->vocabularyStatsRepository->profileSummary();
        $grammar = $this->grammarStatsRepository->profileSummary();

        $readTomes = $manga['read'];
        $completedSeries = $this->completedSeries();
        $tomeXp = $manga['rewarded_tomes'] * UserXp::READ_TOME;
        $seriesXp = $manga['rewarded_series'] * UserXp::COMPLETE_SERIES;
        $readArtbooks = $artbook['read'];
        $artbookXp = $artbook['rewarded'] * UserXp::READ_ARTBOOK;
        $figurinesCollected = $figurine['collected'];
        $figurinesXp = $figurine['rewarded'] * UserXp::COLLECT_FIGURINE;
        $nendoroidsCollected = $nendoroid['collected'];
        $nendoroidsXp = $nendoroid['rewarded'] * UserXp::COLLECT_NENDOROID;
        $peluchesCollected = $peluche['collected'];
        $peluchesXp = $peluche['rewarded'] * UserXp::COLLECT_PELUCHE;
        $vocabularyLearned = $vocabulary['mastered'];
        $grammarLearned = $grammar['mastered'];
        $vocabularyXp = $vocabulary['rewarded'] * UserXp::LEARN_VOCABULARY;
        $grammarXp = $grammar['rewarded'] * UserXp::LEARN_GRAMMAR;

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

            peluchesCollected: $peluchesCollected,
            peluchesXp: $peluchesXp,

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
                + $peluchesXp
                + $vocabularyXp
                + $grammarXp,
        );
    }
}
