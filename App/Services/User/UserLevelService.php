<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

/**
 * Gestion des niveaux et de l'expérience utilisateur.
 */
final readonly class UserLevelService
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    /**
     * XP nécessaire pour atteindre le niveau suivant.
     */
    public function xpRequiredForLevel(
        int $level,
    ): int {
        return max(
            1,
            $level * 5,
        );
    }

    /**
     * Progression du niveau actuel.
     */
    public function progress(
        User $user,
    ): float {

        $required =
            $this->xpRequiredForLevel(
                $user->level,
            );

        return min(
            100,
            (
                $user->xp
                / $required
            ) * 100,
        );
    }

    /**
     * Ajoute de l'expérience à un utilisateur.
     */
    public function addXp(
        User $user,
        int $xp,
    ): void {

        if ($xp <= 0)
        {
            return;
        }

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