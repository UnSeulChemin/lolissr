<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

final readonly class UserLevelService
{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | LEVELS
    |--------------------------------------------------------------------------
    */

    public function xpRequiredForLevel(int $level): int
    {
        return max(1, $level * 5);
    }

    public function progress(User $user): float
    {
        $required = $this->xpRequiredForLevel($user->level);

        return min(100, ($user->xp / $required) * 100);
    }

    public function addXp(User $user, int $xp): void
    {
        if ($xp <= 0)
        {
            return;
        }

        $user->xp += $xp;

        while ($user->xp >= $this->xpRequiredForLevel($user->level))
        {
            $required = $this->xpRequiredForLevel($user->level);

            $user->xp -= $required;
            $user->level++;
        }

        $this->repository->updateLevelAndXp($user->id, $user->level, $user->xp);
    }
}
