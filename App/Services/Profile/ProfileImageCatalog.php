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
    ];

    public const FIGURINE_REWARD_FRAME = 'ailes-roses';
    public const FIGURINE_REWARD_TARGET = 8;
    public const ARTBOOK_REWARD_FRAME = 'enluminure';
    public const ARTBOOK_REWARD_TARGET = 25;

    /** @return list<array{frame: string, frame_extension: string, required_level: int, unlocked: bool, requirement: string}> */
    public function framesForLevel(int $level, int $figurinesCollected = 0, int $readArtbooks = 0): array
    {
        $frames = [];

        foreach ($this->items('frame') as $item)
        {
            $requiredLevel = self::FRAME_LEVELS[$item['frame']] ?? 1;
            $isReward = $item['frame'] === self::FIGURINE_REWARD_FRAME;
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
            $groupA = $a['required_level'] === 0 ? ($a['unlocked'] ? 0 : 2) : 1;
            $groupB = $b['required_level'] === 0 ? ($b['unlocked'] ? 0 : 2) : 1;
            if ($groupA !== $groupB)
            {
                return $groupA <=> $groupB;
            }

            $comparison = $a['required_level'] <=> $b['required_level'];

            return $comparison !== 0 ? $comparison : strcmp($a['frame'], $b['frame']);
        });

        return $frames;
    }

    /** @return list<array{banner: string, banner_extension: string, required_level: int, unlocked: bool}> */
    public function bannersForLevel(int $level): array
    {
        $banners = [];

        foreach ($this->items('banner') as $item)
        {
            $requiredLevel = $item['banner'] === 'lune' ? 10 : 1;
            $banners[] = [
                'banner' => $item['banner'],
                'banner_extension' => $item['banner_extension'],
                'required_level' => $requiredLevel,
                'unlocked' => $level >= $requiredLevel,
            ];
        }

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
