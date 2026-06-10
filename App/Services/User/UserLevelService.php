<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

/**
 * =========================================
 * USER LEVEL SERVICE
 * =========================================
 *
 * Responsable de :
 *
 * - Calcul du niveau
 * - Calcul de la progression
 * - Gestion des gains d'XP
 * - Passage des niveaux
 */
final readonly class UserLevelService
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    /**
     * XP nécessaire pour atteindre
     * le niveau suivant.
     *
     * Niveau 1 -> 5 XP
     * Niveau 2 -> 10 XP
     * Niveau 3 -> 15 XP
     * etc.
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
     * Ajoute de l'XP à un utilisateur.
     *
     * Gère automatiquement :
     *
     * - les montées de niveau
     * - les montées multiples
     * - la sauvegarde en base
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

        while (
            $user->xp >= $this->xpRequiredForLevel(
                $user->level,
            )
        ) {

            $user->xp -=
                $this->xpRequiredForLevel(
                    $user->level,
                );

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