<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\Constants\UserXp;
use App\Models\Nendoroid;
use App\Repositories\Nendoroid\NendoroidRepository;
use App\Services\User\UserLevelService;

final readonly class NendoroidXpRewardService
{
    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private UserLevelService $userLevelService,
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

        $this->achievementXpService->rewardNendoroids($user, $this->nendoroidStatsRepository->profileSummary()['collected']);

        if (! $this->nendoroidRepository->claimCollectReward($nendoroid->id))
        {
            return false;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::COLLECT_NENDOROID
        );

        return true;
    }
}
