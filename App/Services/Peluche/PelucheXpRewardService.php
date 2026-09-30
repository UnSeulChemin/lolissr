<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\Constants\UserXp;
use App\Models\Peluche;
use App\Repositories\Peluche\PelucheRepository;

final readonly class PelucheXpRewardService
{
    public function __construct(
        private PelucheRepository $pelucheRepository,
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

        $xpEarned = $this->pelucheRepository->claimCollectReward($peluche->id);

        $this->achievementXpService->rewardPeluches($user, $this->pelucheStatsRepository->countCollected(), $xpEarned ? UserXp::COLLECT_PELUCHE : 0);

        return $xpEarned;
    }
}
