<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\UserXp;
use App\Models\Manga;
use App\Repositories\Manga\MangaRepository;
use App\Services\User\UserLevelService;

final readonly class MangaXpRewardService
{
    public function __construct(
        private MangaRepository $mangaRepository,
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

        $xpEarned = $this->mangaRepository->claimReadReward($manga->id);

        if ($xpEarned)
        {
            $this->userLevelService->addXp(
                $user,
                UserXp::READ_TOME
            );
        }

        $seriesXpEarned = false;

        if ($this->mangaRepository->claimSeriesReward($slug))
        {
            $this->userLevelService->addXp(
                $user,
                UserXp::COMPLETE_SERIES
            );

            $seriesXpEarned = true;
        }

        return [
            'xpEarned' => $xpEarned,
            'seriesXpEarned' => $seriesXpEarned,
        ];
    }
}
