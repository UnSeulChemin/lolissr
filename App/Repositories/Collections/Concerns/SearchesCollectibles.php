<?php

declare(strict_types=1);

namespace App\Repositories\Collections\Concerns;

use Framework\Support\Strings;

trait SearchesCollectibles
{
    /**
     * @template T of object
     * @param class-string<T> $model
     * @return list<T>
     */
    private function searchCollectibles(string $search, string $model, int $limit = 20): array
    {
        $search = trim(preg_replace('/\s+/', ' ', trim($search)) ?? '');
        if ($search === '') return [];

        $limit = max(1, min(20, $limit));
        $slug = Strings::slug($search);

        return $this->fetchAll(
            "SELECT slug, numero, origin, waifu, thumbnail, extension
             FROM {$this->readTable()}
             WHERE (waifu LIKE :search_waifu OR origin LIKE :search_origin OR slug LIKE :search_slug)
             ORDER BY origin ASC, waifu ASC, numero ASC, id ASC LIMIT {$limit}",
            [
                'search_waifu' => "%{$search}%",
                'search_origin' => "%{$search}%",
                'search_slug' => $slug !== '' ? '%' . $slug . '%' : null
            ],
            $model
        );
    }
}
