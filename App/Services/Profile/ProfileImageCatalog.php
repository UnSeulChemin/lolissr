<?php

declare(strict_types=1);

namespace App\Services\Profile;

final class ProfileImageCatalog
{
    // Explicit levels keep existing unlocks stable when new images are added.
    // Reward frames unlock every 25 levels.
    private const FRAME_LEVELS = [
        'default' => 1,
        'amethyste' => 25,
        'saphir' => 50,
        'rubis' => 75,
        'diamant' => 100,
        'emeraude' => 125,
        'obsidienne' => 150,
        'aurore' => 175,
        'imperial' => 200,
        'ailes-azur' => 100,
        'ailes-souveraines' => 200,
    ];

    public const FIGURINE_REWARD_FRAME = 'ailes-roses';
    public const FIGURINE_REWARD_TARGET = 8;
    public const ARTBOOK_REWARD_FRAME = 'enluminure';
    public const ARTBOOK_REWARD_TARGET = 25;
    public const TOME_REWARD_BANNER = 'lectrice-lunaire';
    public const TOME_REWARD_TARGET = 100;
    public const TOME_REWARD_FRAME = 'grimoire-celeste';
    public const TOME_FRAME_TARGET = 200;
    public const NENDOROID_REWARD_BANNER = 'petit-monde';
    public const NENDOROID_REWARD_TARGET = 10;
    public const NENDOROID_REWARD_FRAME = 'ecrin-des-merveilles';
    public const NENDOROID_FRAME_TARGET = 50;
    public const PELUCHE_REWARD_BANNER = 'refuge-des-peluches';
    public const PELUCHE_BANNER_TARGET = 10;
    public const PELUCHE_REWARD_FRAME = 'cocon-dore';
    public const PELUCHE_FRAME_TARGET = 25;
    public const VOCABULARY_REWARD_BANNER = 'bibliotheque-des-mots';
    public const GRAMMAR_REWARD_BANNER = 'atelier-des-phrases';
    public const VOCABULARY_REWARD_FRAME = 'lexique-de-jade';
    public const GRAMMAR_REWARD_FRAME = 'plume-astrale';
    public const LEARNING_BANNER_TARGET = 100;
    public const LEARNING_FRAME_TARGET = 200;
    public const LEVEL_REWARD_BANNER = 'palais-des-etoiles';
    public const LEVEL_BANNER_TARGET = 25;
    // Add future level reward banners here; unlocks and picker ordering use this map.
    public const LEVEL_REWARD_BANNERS = [self::LEVEL_BANNER_TARGET => self::LEVEL_REWARD_BANNER];
    public const LEVEL_REWARD_FRAMES = [100 => 'ailes-azur', 200 => 'ailes-souveraines'];

    /** @return list<array{frame: string, frame_extension: string, required_level: int, unlocked: bool, requirement: string}> */
    public function framesForLevel(int $level, int $figurinesCollected = 0, int $readArtbooks = 0, int $readTomes = 0, int $nendoroidsCollected = 0, int $peluchesCollected = 0, int $vocabularyLearned = 0, int $grammarLearned = 0): array
    {
        $frames = [];

        foreach ($this->items('frame') as $item)
        {
            if (in_array($item['frame'], [self::VOCABULARY_REWARD_FRAME, self::GRAMMAR_REWARD_FRAME], true))
            {
                $isVocabulary = $item['frame'] === self::VOCABULARY_REWARD_FRAME;
                $frames[] = [
                    'frame' => $item['frame'],
                    'frame_extension' => $item['frame_extension'],
                    'required_level' => 0,
                    'unlocked' => ($isVocabulary ? $vocabularyLearned : $grammarLearned) >= self::LEARNING_FRAME_TARGET,
                    'requirement' => self::LEARNING_FRAME_TARGET . ($isVocabulary ? ' mots maîtrisés' : ' points de grammaire maîtrisés'),
                ];
                continue;
            }
            if ($item['frame'] === self::PELUCHE_REWARD_FRAME)
            {
                $frames[] = [
                    'frame' => $item['frame'],
                    'frame_extension' => $item['frame_extension'],
                    'required_level' => 0,
                    'unlocked' => $peluchesCollected >= self::PELUCHE_FRAME_TARGET,
                    'requirement' => self::PELUCHE_FRAME_TARGET . ' peluches collectionnées',
                ];
                continue;
            }
            if ($item['frame'] === self::NENDOROID_REWARD_FRAME)
            {
                $frames[] = [
                    'frame' => $item['frame'],
                    'frame_extension' => $item['frame_extension'],
                    'required_level' => 0,
                    'unlocked' => $nendoroidsCollected >= self::NENDOROID_FRAME_TARGET,
                    'requirement' => self::NENDOROID_FRAME_TARGET . ' nendoroids collectionnés',
                ];
                continue;
            }
            $requiredLevel = self::FRAME_LEVELS[$item['frame']] ?? 1;
            $isReward = $item['frame'] === self::FIGURINE_REWARD_FRAME;
            if ($item['frame'] === self::TOME_REWARD_FRAME)
            {
                $frames[] = [
                    'frame' => $item['frame'],
                    'frame_extension' => $item['frame_extension'],
                    'required_level' => 0,
                    'unlocked' => $readTomes >= self::TOME_FRAME_TARGET,
                    'requirement' => self::TOME_FRAME_TARGET . ' tomes lus',
                ];
                continue;
            }
            if ($item['frame'] === self::ARTBOOK_REWARD_FRAME)
            {
                $frames[] = [
                    'frame' => $item['frame'],
                    'frame_extension' => $item['frame_extension'],
                    'required_level' => 0,
                    'unlocked' => $readArtbooks >= self::ARTBOOK_REWARD_TARGET,
                    'requirement' => self::ARTBOOK_REWARD_TARGET . ' artbooks lus',
                ];
                continue;
            }
            $frames[] = [
                'frame' => $item['frame'],
                'frame_extension' => $item['frame_extension'],
                'required_level' => $isReward ? 0 : $requiredLevel,
                'unlocked' => $isReward ? $figurinesCollected >= self::FIGURINE_REWARD_TARGET : $level >= $requiredLevel,
                'requirement' => $isReward ? self::FIGURINE_REWARD_TARGET . ' figurines collectionnées' : ($requiredLevel > 1 ? 'Niveau ' . $requiredLevel : 'Disponible'),
            ];
        }

        usort($frames, static function (array $a, array $b): int
        {
            $rewardA = $a['required_level'] === 0 || in_array($a['frame'], self::LEVEL_REWARD_FRAMES, true);
            $rewardB = $b['required_level'] === 0 || in_array($b['frame'], self::LEVEL_REWARD_FRAMES, true);
            $groupA = $rewardA ? ($a['unlocked'] ? 0 : 2) : 1;
            $groupB = $rewardB ? ($b['unlocked'] ? 0 : 2) : 1;
            // Keep collection rewards first, then level rewards in descending order.
            if ($groupA === $groupB && $rewardA && $rewardB)
            {
                $levelOrder = ($a['required_level'] > 0) <=> ($b['required_level'] > 0);
                if ($levelOrder !== 0)
                {
                    return $levelOrder;
                }
                if ($a['required_level'] > 0 && $b['required_level'] > 0)
                {
                    return $b['required_level'] <=> $a['required_level'];
                }
            }
            if ($groupA !== $groupB)
            {
                return $groupA <=> $groupB;
            }

            $comparison = $a['required_level'] <=> $b['required_level'];

            return $comparison !== 0 ? $comparison : strcmp($a['frame'], $b['frame']);
        });

        return $frames;
    }

    /** @return list<array{banner: string, banner_extension: string, required_level: int, unlocked: bool, requirement: string}> */
    public function bannersForLevel(int $level, int $readTomes = 0, int $nendoroidsCollected = 0, int $peluchesCollected = 0, int $vocabularyLearned = 0, int $grammarLearned = 0): array
    {
        $banners = [];

        foreach ($this->items('banner') as $item)
        {
            if (in_array($item['banner'], [self::VOCABULARY_REWARD_BANNER, self::GRAMMAR_REWARD_BANNER], true))
            {
                $isVocabulary = $item['banner'] === self::VOCABULARY_REWARD_BANNER;
                $banners[] = [
                    'banner' => $item['banner'],
                    'banner_extension' => $item['banner_extension'],
                    'required_level' => 0,
                    'unlocked' => ($isVocabulary ? $vocabularyLearned : $grammarLearned) >= self::LEARNING_BANNER_TARGET,
                    'requirement' => self::LEARNING_BANNER_TARGET . ($isVocabulary ? ' mots maîtrisés' : ' points de grammaire maîtrisés'),
                ];
                continue;
            }
            if ($item['banner'] === self::PELUCHE_REWARD_BANNER)
            {
                $banners[] = [
                    'banner' => $item['banner'],
                    'banner_extension' => $item['banner_extension'],
                    'required_level' => 0,
                    'unlocked' => $peluchesCollected >= self::PELUCHE_BANNER_TARGET,
                    'requirement' => self::PELUCHE_BANNER_TARGET . ' peluches collectionnées',
                ];
                continue;
            }
            if ($item['banner'] === self::NENDOROID_REWARD_BANNER)
            {
                $banners[] = [
                    'banner' => $item['banner'],
                    'banner_extension' => $item['banner_extension'],
                    'required_level' => 0,
                    'unlocked' => $nendoroidsCollected >= self::NENDOROID_REWARD_TARGET,
                    'requirement' => self::NENDOROID_REWARD_TARGET . ' nendoroids collectionnés',
                ];
                continue;
            }
            $rewardLevel = array_search($item['banner'], self::LEVEL_REWARD_BANNERS, true);
            $requiredLevel = $rewardLevel !== false ? $rewardLevel : ($item['banner'] === 'lune' ? 10 : 1);
            $isReward = $item['banner'] === self::TOME_REWARD_BANNER;
            $banners[] = [
                'banner' => $item['banner'],
                'banner_extension' => $item['banner_extension'],
                'required_level' => $isReward ? 0 : $requiredLevel,
                'unlocked' => $isReward ? $readTomes >= self::TOME_REWARD_TARGET : $level >= $requiredLevel,
                'requirement' => $isReward ? self::TOME_REWARD_TARGET . ' tomes lus' : ($requiredLevel > 1 ? 'Niveau ' . $requiredLevel : 'Disponible'),
            ];
        }

        usort($banners, static function (array $a, array $b): int
        {
            $rewardA = $a['required_level'] === 0 || in_array($a['banner'], self::LEVEL_REWARD_BANNERS, true);
            $rewardB = $b['required_level'] === 0 || in_array($b['banner'], self::LEVEL_REWARD_BANNERS, true);
            $groupA = $rewardA ? ($a['unlocked'] ? 0 : 2) : 1;
            $groupB = $rewardB ? ($b['unlocked'] ? 0 : 2) : 1;
            // Keep collection rewards first, then level rewards in descending order.
            if ($groupA === $groupB && $rewardA && $rewardB)
            {
                $levelOrder = ($a['required_level'] > 0) <=> ($b['required_level'] > 0);
                if ($levelOrder !== 0)
                {
                    return $levelOrder;
                }
                if ($a['required_level'] > 0 && $b['required_level'] > 0)
                {
                    return $b['required_level'] <=> $a['required_level'];
                }
            }
            return $groupA !== $groupB ? $groupA <=> $groupB : $a['required_level'] <=> $b['required_level'];
        });

        return $banners;
    }

    /** @return list<array<string, string>> */
    public function items(string $type): array
    {
        if (! in_array($type, ['avatar', 'banner', 'frame'], true))
        {
            throw new \InvalidArgumentException('Unknown profile image type.');
        }

        $path = dirname(__DIR__, 3) . '/public/images/profil/' . $type . '/thumbnail';
        $files = glob($path . '/*.{webp,jpg,png}', GLOB_BRACE);
        $items = [];

        foreach ($files === false ? [] : $files as $file)
        {
            $items[] = [
                $type => pathinfo($file, PATHINFO_FILENAME),
                $type . '_extension' => pathinfo($file, PATHINFO_EXTENSION),
            ];
        }

        return $items;
    }
}
