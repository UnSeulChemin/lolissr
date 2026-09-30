<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Constants\AchievementRewards;
use App\DTO\Profile\ProfileStatsData;
use App\DTO\Profile\ProfileUnlockStatsData;

/** @phpstan-type Achievement array{category: string, icon: string, title: string, current: int, target: int, unlocked: bool} */
final class ProfileAchievements
{
    /** @return list<Achievement> */
    public static function forStats(ProfileStatsData|ProfileUnlockStatsData $stats, int $level): array
    {
        $categories = [
            ['Tomes', '📚', 'tomes lus', $stats->readTomes, array_keys(AchievementRewards::TOMES)],
            ['Séries', '📖', 'séries terminées', $stats->completedSeries, array_keys(AchievementRewards::SERIES)],
            ['Artbooks', '📕', 'artbooks lus', $stats->readArtbooks, array_keys(AchievementRewards::ARTBOOKS)],
            ['Figurines', '🎀', 'figurines collectionnées', $stats->figurinesCollected, array_keys(AchievementRewards::FIGURINES)],
            ['Nendoroids', '🪆', 'nendoroids collectionnés', $stats->nendoroidsCollected, array_keys(AchievementRewards::NENDOROIDS)],
            ['Peluches', '🧸', 'peluches collectionnées', $stats->peluchesCollected, array_keys(AchievementRewards::PELUCHES)],
            ['Vocabulaire', '🎓', 'mots maîtrisés', $stats->vocabularyLearned, array_keys(AchievementRewards::VOCABULARY)],
            ['Grammaire', '📝', 'points de grammaire maîtrisés', $stats->grammarLearned, array_keys(AchievementRewards::GRAMMAR)],
            ['Niveau', '⭐', 'Niveau', $level, AchievementRewards::LEVELS],
        ];
        $achievements = [];

        foreach ($categories as [$category, $icon, $label, $current, $targets])
        {
            foreach ($targets as $target)
            {
                $achievements[] = [
                    'category' => $category,
                    'icon' => $icon,
                    'title' => $category === 'Niveau' ? 'Niveau ' . $target : $target . ' ' . $label,
                    'current' => max(0, $current),
                    'target' => $target,
                    'unlocked' => $current >= $target,
                ];
            }
        }

        return $achievements;
    }
}
