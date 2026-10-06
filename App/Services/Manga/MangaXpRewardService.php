<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Constants\Profile\UserXp;
use App\Models\Manga\Manga;
use App\Repositories\Manga\MangaRepository;

final readonly class MangaXpRewardService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private \App\Services\Profile\AchievementXpService $achievementXpService,
        private \App\Repositories\Manga\MangaStatsRepository $mangaStatsRepository
    )
    {
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
            return ['xpEarned' => false, 'seriesXpEarned' => false];
        }

        $xpEarned = $this->mangaRepository->claimReadReward($manga->id);

        $seriesXpEarned = $this->mangaRepository->claimSeriesReward($slug);
        $this->achievementXpService->rewardManga(
            $user,
            $this->mangaStatsRepository->countRead(),
            $this->mangaStatsRepository->countCompletedSeries(),
            ($xpEarned ? UserXp::READ_TOME : 0) + ($seriesXpEarned ? UserXp::COMPLETE_SERIES : 0)
        );

        return ['xpEarned' => $xpEarned, 'seriesXpEarned' => $seriesXpEarned];
    }

    // Modifier le statut ou supprimer un tome non lu peut aussi terminer une série.
    public function rewardSeriesAchievements(): void
    {
        $user = user();
        if ($user !== null)
        {
            $target = $this->achievementXpService->pendingSeriesTarget($user);
            if ($target === 0) return;
            $this->achievementXpService->rewardSeries($user, $this->mangaStatsRepository->countCompletedSeries($target));
        }
    }
}
