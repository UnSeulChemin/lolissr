<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\Constants\UserXp;
use App\Models\Nendoroid;
use App\Repositories\Nendoroid\NendoroidRepository;

final readonly class NendoroidXpRewardService
{
    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Nendoroid\NendoroidStatsRepository $nendoroidStatsRepository,
    ) {
    }

    public function rewardCollect(
        Nendoroid $nendoroid
    ): bool {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        $xpEarned = $this->nendoroidRepository->claimCollectReward($nendoroid->id);

        $this->achievementXpService->rewardNendoroids($user, $this->nendoroidStatsRepository->countCollected(), $xpEarned ? UserXp::COLLECT_NENDOROID : 0);

        return $xpEarned;
    }
}
