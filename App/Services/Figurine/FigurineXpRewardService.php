<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\Constants\UserXp;
use App\Models\Figurine;
use App\Repositories\Figurine\FigurineRepository;

final readonly class FigurineXpRewardService
{
    public function __construct(
        private FigurineRepository $figurineRepository,
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

        $xpEarned = $this->figurineRepository->claimCollectReward($figurine->id);

        $this->achievementXpService->rewardFigurines($user, $this->figurineStatsRepository->countCollected(), $xpEarned ? UserXp::COLLECT_FIGURINE : 0);

        return $xpEarned;
    }
}
