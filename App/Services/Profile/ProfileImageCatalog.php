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

    /** @return list<array{frame: string, frame_extension: string, required_level: int, unlocked: bool}> */
    public function framesForLevel(int $level): array
    {
        $frames = [];

        foreach ($this->items('frame') as $item)
        {
            $requiredLevel = self::FRAME_LEVELS[$item['frame']] ?? 1;
            $frames[] = [
                'frame' => $item['frame'],
                'frame_extension' => $item['frame_extension'],
                'required_level' => $requiredLevel,
                'unlocked' => $level >= $requiredLevel,
            ];
        }

        usort($frames, static function (array $a, array $b): int
        {
            $comparison = $a['required_level'] <=> $b['required_level'];

            return $comparison !== 0 ? $comparison : strcmp($a['frame'], $b['frame']);
        });

        return $frames;
    }

    /** @return list<array<string, string>> */
    public function items(string $type): array
    {
        if (! in_array($type, ['avatar', 'banner', 'frame'], true))
        {
            throw new \InvalidArgumentException('Unknown profile image type.');
        }

        $path = dirname(__DIR__, 3) . '/public/images/' . $type . '/thumbnail';
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
