<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * =========================================
 * USER XP REWARDS
 * =========================================
 *
 * Toutes les récompenses d'XP du projet.
 *
 * Ne jamais utiliser de valeurs magiques
 * directement dans les services,
 * contrôleurs ou repositories.
 *
 * Exemple :
 *
 * $this->userLevelService->addXp(
 *     $user,
 *     UserXp::READ_TOME,
 * );
 */
final class UserXp
{
    /**
     * Lecture d'un tome.
     */
    public const READ_TOME = 5;

    /**
     * Série complétée.
     */
    public const COMPLETE_SERIES = 20;

    /**
     * Mot de vocabulaire appris.
     */
    public const LEARN_VOCABULARY = 5;

    /**
     * Point de grammaire appris.
     */
    public const LEARN_GRAMMAR = 5;
}