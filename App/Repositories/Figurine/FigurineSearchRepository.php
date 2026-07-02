<?php

declare(strict_types=1);

namespace App\Repositories\Figurine;

use App\Models\Figurine;
use App\Models\Model;

use Framework\Support\Str;

final class FigurineSearchRepository extends Model
{
    protected string $table = 'figurine';

    /**
     * @return list<Figurine>
     */
    public function search(string $search): array
    {
        $search = $this->normalizeSearch($search);

        if ($search === '')
        {
            return [];
        }

        return $this->fetchSearchResults($search);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function normalizeSearch(string $search): string
    {
        return trim(
            preg_replace('/\s+/', ' ', trim($search)) ?? ''
        );
    }

    private function slugSearch(string $search): string
    {
        return Str::slug($search);
    }

    /**
     * @return list<Figurine>
     */
    private function fetchSearchResults(string $search): array
    {
        $sql = "
            SELECT *
            FROM {$this->table()}
            WHERE (
                waifu LIKE :search_waifu
                OR origin LIKE :search_origin
                OR slug LIKE :search_slug
            )
            ORDER BY origin ASC, waifu ASC, numero ASC
        ";

        /** @var list<Figurine> $figurines */
        $figurines = $this->fetchAll(
            $sql,
            [
                'search_waifu' => "%{$search}%",
                'search_origin' => "%{$search}%",
                'search_slug' => '%' . $this->slugSearch($search) . '%',
            ],
            Figurine::class,
        );

        return $figurines;
    }
}