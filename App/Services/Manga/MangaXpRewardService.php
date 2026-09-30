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
        private UserLevelService $userLevelService,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Manga\MangaStatsRepository $mangaStatsRepository,
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
        $this->achievementXpService->rewardManga(
            $user,
            $this->mangaStatsRepository->profileSummary()['read'],
            $this->mangaStatsRepository->countCompletedSeries(),
        );

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

    // Editing a series status or deleting an unread tome can complete a series too.
    public function rewardSeriesAchievements(): void
    {
        $user = user();
        if ($user !== null)
        {
            $this->achievementXpService->rewardSeries($user, $this->mangaStatsRepository->countCompletedSeries());
        }
    }
}
