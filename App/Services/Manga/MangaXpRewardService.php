<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\UserXp;
use App\Models\Manga;
use App\Models\User;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaStatsRepository;
use App\Services\User\UserLevelService;

final readonly class MangaXpRewardService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaStatsRepository $mangaStatsRepository,
        private UserLevelService $userLevelService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | XP REWARDS
    |--------------------------------------------------------------------------
    */

    /**
     * Récompense la lecture d'un tome.
     * Ajoute également le bonus si la série est terminée.
     *
     * @return array{
     *     xpEarned: bool,
     *     seriesXpEarned: bool
     * }
     */
    public function rewardTomeRead(
        Manga $manga,
        string $slug
    ): array {
        $user = user();

        if (! $user instanceof User)
        {
            return $this->noReward();
        }

        if (! $this->mangaRepository->claimReadReward($manga->id))
        {
            return $this->noReward();
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::READ_TOME
        );

        return [
            'xpEarned' => true,
            'seriesXpEarned' => $this->rewardSeriesCompleted(
                $user,
                $slug
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SERIES COMPLETION
    |--------------------------------------------------------------------------
    */

    private function rewardSeriesCompleted(
        User $user,
        string $slug
    ): bool {
        if (
            ! $this->mangaStatsRepository->isSeriesCompleted($slug)
            || ! $this->mangaRepository->claimSeriesReward($slug)
        )
        {
            return false;
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::COMPLETE_SERIES
        );

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | RESULT
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     xpEarned: bool,
     *     seriesXpEarned: bool
     * }
     */
    private function noReward(): array
    {
        return [
            'xpEarned' => false,
            'seriesXpEarned' => false,
        ];
    }
}