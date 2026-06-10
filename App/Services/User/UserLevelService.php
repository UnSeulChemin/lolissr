<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

final readonly class UserLevelService
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    public function xpRequiredForLevel(
        int $level,
    ): int {
        return $level * 5;
    }

    public function progress(
        User $user,
    ): float {

        $required =
            $this->xpRequiredForLevel(
                $user->level,
            );

        if ($required <= 0)
        {
            return 0;
        }

        return min(
            100,
            (
                $user->xp
                / $required
            ) * 100,
        );
    }

    public function xpRemaining(
        User $user,
    ): int {

        return max(
            0,
            $this->xpRequiredForLevel(
                $user->level,
            )
            - $user->xp,
        );
    }

    public function addXp(
        User $user,
        int $xp,
    ): void {

        $user->xp += $xp;

        while (true)
        {
            $required =
                $this->xpRequiredForLevel(
                    $user->level,
                );

            if ($user->xp < $required)
            {
                break;
            }

            $user->xp -= $required;

            $user->level++;
        }

        $this->repository
            ->updateLevelAndXp(
                $user->id,
                $user->level,
                $user->xp,
            );
    }
}