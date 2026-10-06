<?php

declare(strict_types=1);

namespace App\Constants\Profile;

final class UserXp
{
    // =================================================
    // MANGA
    // =================================================

    public const READ_TOME = 20;
    public const COMPLETE_SERIES = 100;

    // =================================================
    // ARTBOOK
    // =================================================

    public const READ_ARTBOOK = 200;

    // =================================================
    // FIGURINE
    // =================================================

    public const COLLECT_FIGURINE = 800;

    // =================================================
    // NENDOROID
    // =================================================

    public const COLLECT_NENDOROID = 100;

    // =================================================
    // PELUCHE
    // =================================================

    public const COLLECT_PELUCHE = 100;

    // =================================================
    // CHINOIS
    // =================================================

    public const LEARN_VOCABULARY = 20;
    public const LEARN_GRAMMAR = 50;

    private function __construct()
    {
    }
}
