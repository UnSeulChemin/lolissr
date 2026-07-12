<?php

declare(strict_types=1);

namespace App\Repositories\Peluche;

use App\Models\Model;
use App\Models\Peluche;

use Framework\Support\Str;

final class PelucheSearchRepository extends Model
{
    protected string $table = 'peluche';

    /**
     * @return list<Peluche>
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
     * @return list<Peluche>
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

        /** @var list<Peluche> $peluches */
        $peluches = $this->fetchAll(
            $sql,
            [
                'search_waifu' => "%{$search}%",
                'search_origin' => "%{$search}%",
                'search_slug' => '%' . $this->slugSearch($search) . '%',
            ],
            Peluche::class,
        );

        return $peluches;
    }
}