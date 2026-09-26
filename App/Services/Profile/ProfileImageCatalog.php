<?php

declare(strict_types=1);

namespace App\Services\Profile;

final class ProfileImageCatalog
{
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
