<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\Constants\UserXp;
use App\Models\Peluche;
use App\Repositories\Peluche\PelucheRepository;
use App\Services\User\UserLevelService;

final readonly class PelucheXpRewardService
{
    public function __construct(
        private PelucheRepository $pelucheRepository,
        private UserLevelService $userLevelService,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Peluche\PelucheStatsRepository $pelucheStatsRepository,
    ) {
    }


    public function rewardCollect(
        Peluche $peluche
    ): bool {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        $this->achievementXpService->rewardPeluches($user, $this->pelucheStatsRepository->profileSummary()['collected']);

        if (! $this->pelucheRepository->claimCollectReward($peluche->id))
        {
            return false;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::COLLECT_PELUCHE
        );

        return true;
    }
}
