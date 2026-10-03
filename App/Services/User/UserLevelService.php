<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

final readonly class UserLevelService
{
    public function __construct(private UserRepository $repository, private \Framework\Database\Database $database)
    {
    }

    // --------------------------------------------------------------------------
    // NIVEAUX
    // --------------------------------------------------------------------------

    public function xpRequiredForLevel(int $level): int
    {
        return max(1, $level * 5);
    }

    public function progress(User $user): float
    {
        $required = $this->xpRequiredForLevel($user->level);

        return min(100, ($user->xp / $required) * 100);
    }

    /**
     * Calculer et enregistrer les récompenses sous le même verrou utilisateur que la mise à jour des XP.
     * @param callable(): int $computeXp
     */
    public function addComputedXp(User $user, callable $computeXp): void
    {
        $update = function () use ($user, $computeXp): User
        {
            $current = $this->repository->lockLevelAndXp($user->id);
            if ($current === null) throw new \RuntimeException('Utilisateur introuvable pour les XP.');
            $xp = $computeXp();
            if ($xp <= 0) return $current;
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

        if ($this->database->inTransaction())
        {
            $level = $user->level;
            $currentXp = $user->xp;
            $this->database->onRollback(static function () use ($user, $level, $currentXp): void
            {
                $user->level = $level;
                $user->xp = $currentXp;
            });
            $updated = $update();
        }
        else
        {
            $updated = $this->database->transaction($update);
        }
        $user->level = $updated->level;
        $user->xp = $updated->xp;
    }
}
