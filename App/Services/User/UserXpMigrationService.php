<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Constants\UserXp;
use App\Repositories\Manga\MangaRepository;

final readonly class UserXpMigrationService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private UserLevelService $userLevelService,
    ) {
    }

    public function migrate(): void
    {
        $user = user();

        if ($user === null)
        {
            return;
        }

        $mangas =
            $this->mangaRepository
                ->findReadWithoutReward();

        foreach ($mangas as $manga)
        {
            $this->userLevelService
                ->addXp(
                    $user,
                    UserXp::READ_TOME,
                );

            $this->mangaRepository
                ->markXpRewarded(
                    $manga->id,
                );
        }
    }
}