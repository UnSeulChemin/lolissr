<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\Constants\UserXp;
use App\Models\Figurine;
use App\Repositories\Figurine\FigurineRepository;
use App\Services\User\UserLevelService;

final readonly class FigurineXpRewardService
{
    public function __construct(
        private FigurineRepository $figurineRepository,
        private UserLevelService $userLevelService,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Figurine\FigurineStatsRepository $figurineStatsRepository,
    ) {
    }

    public function rewardCollect(
        Figurine $figurine
    ): bool {
        $user = user();

        if ($user === null)
        {
            return false;
        }

        $this->achievementXpService->rewardFigurines($user, $this->figurineStatsRepository->countCollected());

        if (! $this->figurineRepository->claimCollectReward($figurine->id))
        {
            return false;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::COLLECT_FIGURINE
        );

        return true;
    }
}
