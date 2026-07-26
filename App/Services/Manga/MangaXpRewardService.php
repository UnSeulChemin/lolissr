<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\UserXp;
use App\Models\Manga;
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

    /**
     * @return array{
     *     xpEarned: bool,
     *     seriesXpEarned: bool
     * }
     */
    public function rewardRead(Manga $manga, string $slug): array
    {
        $user = user();

        if ($user === null)
        {
            return [
                'xpEarned' => false,
                'seriesXpEarned' => false,
            ];
        }

        if (! $this->mangaRepository->claimReadReward($manga->id))
        {
            return [
                'xpEarned' => false,
                'seriesXpEarned' => false,
            ];
        }

        $this->userLevelService->addXp(
            $user,
            UserXp::READ_TOME
        );

        $seriesXpEarned = false;

        if (
            $this->mangaStatsRepository->isSeriesCompleted($slug)
            && $this->mangaRepository->claimSeriesReward($slug)
        )
        {
            $this->userLevelService->addXp(
                $user,
                UserXp::COMPLETE_SERIES
            );

            $seriesXpEarned = true;
        }

        return [
            'xpEarned' => true,
            'seriesXpEarned' => $seriesXpEarned,
        ];
    }
}