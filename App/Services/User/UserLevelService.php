<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

final readonly class UserLevelService
{
    public function __construct(
        private UserRepository $repository,
        private \Framework\Database\Database $database
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

        $update = function () use ($user, $xp): User
        {
            $current = $this->repository->lockLevelAndXp($user->id);
            if ($current === null) throw new \RuntimeException('Utilisateur introuvable pour les XP.');
            $current->xp += $xp;
            while ($current->xp >= $this->xpRequiredForLevel($current->level))
            {
                $current->xp -= $this->xpRequiredForLevel($current->level);
                $current->level++;
            }
            if (! $this->repository->updateLevelAndXp($current->id, $current->level, $current->xp))
            {
                throw new \RuntimeException('Impossible de sauvegarder les XP.');
            }
            return $current;
        };

        $updated = $this->database->inTransaction() ? $update() : $this->database->transaction($update);
        $user->level = $updated->level;
        $user->xp = $updated->xp;
    }
}
