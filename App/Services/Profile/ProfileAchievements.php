<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\DTO\Profile\ProfileStatsData;

/** @phpstan-type Achievement array{category: string, icon: string, title: string, current: int, target: int, unlocked: bool} */
final class ProfileAchievements
{
    /** @return list<Achievement> */
    public static function forStats(ProfileStatsData $stats, int $level): array
    {
        $categories = [
            ['Lecture', '📚', 'tomes lus', $stats->readTomes, [1, 10, 25, 50, 100, 200]],
            ['Séries', '📖', 'séries terminées', $stats->completedSeries, [1, 10, 25, 50]],
            ['Artbooks', '🎨', 'artbooks lus', $stats->readArtbooks, [1, 10, 25]],
            ['Figurines', '🎀', 'figurines collectionnées', $stats->figurinesCollected, [1, 4, 8]],
            ['Nendoroids', '🪆', 'nendoroids collectionnés', $stats->nendoroidsCollected, [1, 10, 25, 50]],
            ['Peluches', '🧸', 'peluches collectionnées', $stats->peluchesCollected, [1, 10, 50]],
            ['Vocabulaire', '🎓', 'mots maîtrisés', $stats->vocabularyLearned, [10, 100, 500, 1000]],
            ['Grammaire', '📝', 'points de grammaire maîtrisés', $stats->grammarLearned, [1, 10, 50]],
            ['Niveau', '⭐', 'Niveau', $level, [10, 25, 50, 75, 100]],
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
