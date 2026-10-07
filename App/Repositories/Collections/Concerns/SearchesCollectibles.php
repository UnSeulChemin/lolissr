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
        $search = trim(preg_replace('/\s+/', ' ', \App\Support\Search\SearchQuery::validate($search)) ?? '');
        if ($search === '') return [];

        $limit = max(1, min(20, $limit));
        $slug = Strings::slug($search);

        return $this->fetchAll(
            "SELECT slug, numero, origin, waifu, thumbnail, extension
             FROM {$this->readTable()}
             WHERE (waifu LIKE :search_waifu ESCAPE '!' OR origin LIKE :search_origin ESCAPE '!' OR slug LIKE :search_slug ESCAPE '!')
             ORDER BY origin ASC, waifu ASC, numero ASC, id ASC LIMIT {$limit}",
            [
                'search_waifu' => \App\Support\Search\SearchQuery::containsPattern($search),
                'search_origin' => \App\Support\Search\SearchQuery::containsPattern($search),
                'search_slug' => $slug !== '' ? \App\Support\Search\SearchQuery::containsPattern($slug) : null
            ],
            $model
        );
    }
}
