<?php

declare(strict_types=1);

namespace App\Services\Chinois;

use App\Constants\UserXp;
use App\Repositories\Chinois\ChinoisGrammaireRepository;
use App\Repositories\Chinois\ChinoisVocabulaireRepository;
use App\Services\User\UserLevelService;

final readonly class ChinoisXpRewardService
{
    public function __construct(
        private ChinoisGrammaireRepository $grammaireRepository,
        private ChinoisVocabulaireRepository $vocabulaireRepository,
        private UserLevelService $userLevelService
    ) {
    }

    // =========================================
    // RÉCOMPENSES
    // =========================================

    public function rewardGrammar(int $id): bool
    {
        $user = user();

        if ($user === null || ! $this->grammaireRepository->claimXpReward($id))
        {
            return false;
        }

        $this->userLevelService->addXp($user, UserXp::LEARN_GRAMMAR);

        return true;
    }

    public function rewardVocabulary(int $id): bool
    {
        $user = user();

        if ($user === null || ! $this->vocabulaireRepository->claimXpReward($id))
        {
            return false;
        }

        $this->userLevelService->addXp($user, UserXp::LEARN_VOCABULARY);

        return true;
    }
}