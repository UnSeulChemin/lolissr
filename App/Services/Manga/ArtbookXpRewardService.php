<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\UserXp;
use App\Models\Artbook;
use App\Models\User;
use App\Repositories\Manga\ArtbookRepository;
use App\Services\User\UserLevelService;

final readonly class ArtbookXpRewardService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private UserLevelService $userLevelService
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

        if (! $this->artbookRepository->claimReadReward($artbook->id))
        {
            return false;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::READ_ARTBOOK
        );

        return true;
    }
}