<?php

declare(strict_types=1);

namespace App\Services\Artbook;

use App\Constants\Profile\XpRewards;
use App\Models\Artbook\Artbook;
use App\Models\User\User;
use App\Repositories\Artbook\ArtbookRepository;

final readonly class ArtbookXpRewardService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Artbook\ArtbookStatsRepository $artbookStatsRepository
    )
    {
    }

    // --------------------------------------------------------------------------
    // RÉCOMPENSE XP
    // --------------------------------------------------------------------------

    public function rewardArtbookRead(Artbook $artbook): bool
    {
        $user = user();

        if (! $user instanceof User)
        {
            return false;
        }

        $xpEarned = $this->artbookRepository->claimReadReward($artbook->id);

        $this->achievementXpService->rewardArtbooks($user, $this->artbookStatsRepository->countRead(), $xpEarned ? XpRewards::READ_ARTBOOK : 0);

        return $xpEarned;
    }
}
