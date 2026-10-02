<?php

declare(strict_types=1);

namespace App\Constants;

// Seuils et montants d’XP communs à l’affichage et à l’attribution des succès.
final class AchievementRewards
{
    public const TOMES = [1 => 50, 10 => 500, 25 => 1250, 50 => 2500, 100 => 5000, 200 => 10000];
    public const SERIES = [1 => 50, 10 => 500, 25 => 1250, 50 => 2500];
    public const ARTBOOKS = [1 => 500, 10 => 5000, 25 => 12500];
    public const FIGURINES = [1 => 2000, 4 => 8000, 8 => 16000];
    public const NENDOROIDS = [1 => 250, 10 => 2500, 25 => 6250, 50 => 12500];
    public const PELUCHES = [1 => 250, 10 => 2500, 25 => 6250];
    public const VOCABULARY = [1 => 50, 10 => 500, 25 => 1250, 50 => 2500, 100 => 5000, 200 => 10000];
    public const GRAMMAR = [1 => 50, 10 => 500, 25 => 1250, 50 => 2500, 100 => 5000, 200 => 10000];
    public const LEVELS = [1, 10, 25, 50, 100, 200];

    private function __construct()
    {
    }
}
