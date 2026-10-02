<?php

declare(strict_types=1);

namespace App\Services\Artbook;

use App\Constants\UserXp;
use App\Models\Artbook;
use App\Models\User;
use App\Repositories\Artbook\ArtbookRepository;

final readonly class ArtbookXpRewardService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Artbook\ArtbookStatsRepository $artbookStatsRepository,
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | XP REWARD
    |--------------------------------------------------------------------------
    */

    public function rewardArtbookRead(
        Artbook $artbook
    ): bool {
        $user = user();

        if (! $user instanceof User)
        {
            return false;
        }

        $xpEarned = $this->artbookRepository->claimReadReward($artbook->id);

        $this->achievementXpService->rewardArtbooks($user, $this->artbookStatsRepository->countRead(), $xpEarned ? UserXp::READ_ARTBOOK : 0);

        return $xpEarned;
    }
}
