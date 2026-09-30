<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\UserXp;
use App\Models\Artbook;
use App\Models\User;
use App\Repositories\Manga\ArtbookRepository;

final readonly class ArtbookXpRewardService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Manga\ArtbookStatsRepository $artbookStatsRepository,
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
