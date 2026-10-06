<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Constants\Profile\XpRewards;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;

final readonly class ChinoisXpRewardService
{
    public function __construct(
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Chinois\ChinoisVocabulaireStatsRepository $vocabularyStatsRepository,
        private \App\Repositories\Chinois\ChinoisGrammaireStatsRepository $grammarStatsRepository
    )
    {
    }

    // =================================================
    // RÉCOMPENSES
    // =================================================

    public function rewardGrammar(int $id): bool
    {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        $xpEarned = $this->grammaireRepository->claimXpReward($id);

        $this->achievementXpService->rewardGrammar($user, $this->grammarStatsRepository->countMastered(), $xpEarned ? XpRewards::LEARN_GRAMMAR : 0);

        return $xpEarned;
    }

    public function rewardVocabulary(int $id): bool
    {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        $xpEarned = $this->vocabulaireRepository->claimXpReward($id);

        $this->achievementXpService->rewardVocabulary($user, $this->vocabularyStatsRepository->countMastered(), $xpEarned ? XpRewards::LEARN_VOCABULARY : 0);

        return $xpEarned;
    }
}
