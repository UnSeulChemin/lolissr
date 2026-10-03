<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\UserXp;
use App\DTO\Profile\Responses\ProfileStatsData;
use App\Models\User;
use App\Repositories\Profile\ProfileStatsRepository;

final readonly class ProfileStatsService
{
    public function __construct(private ProfileStatsRepository $repository)
    {
    }

    public function getStats(?User $user = null): ProfileStatsData
    {
        $user ??= user();
        $summary = $this->repository->summary($user?->id);
        $readTomes = $summary['manga_read'];
        $completedSeries = $summary['completed_series'];
        $achievementXp = $summary['achievement_xp'];
        $tomeXp = $summary['manga_rewarded_tomes'] * UserXp::READ_TOME;
        $seriesXp = $summary['manga_rewarded_series'] * UserXp::COMPLETE_SERIES;
        $readArtbooks = $summary['artbook_count'];
        $artbookXp = $summary['artbook_rewarded'] * UserXp::READ_ARTBOOK;
        $figurinesCollected = $summary['figurine_count'];
        $figurinesXp = $summary['figurine_rewarded'] * UserXp::COLLECT_FIGURINE;
        $nendoroidsCollected = $summary['nendoroid_count'];
        $nendoroidsXp = $summary['nendoroid_rewarded'] * UserXp::COLLECT_NENDOROID;
        $peluchesCollected = $summary['peluche_count'];
        $peluchesXp = $summary['peluche_rewarded'] * UserXp::COLLECT_PELUCHE;
        $vocabularyLearned = $summary['chinois_vocabulaire_count'];
        $grammarLearned = $summary['chinois_grammaire_count'];
        $vocabularyXp = $summary['chinois_vocabulaire_rewarded'] * UserXp::LEARN_VOCABULARY;
        $grammarXp = $summary['chinois_grammaire_rewarded'] * UserXp::LEARN_GRAMMAR;

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
                + $grammarXp
                + $achievementXp,
            achievementXp: $achievementXp
        );
    }
}
