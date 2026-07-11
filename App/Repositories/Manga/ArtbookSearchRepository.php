<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Artbook;
use App\Models\Model;

use Framework\Support\Str;

final class ArtbookSearchRepository extends Model
{
    protected string $table = 'artbook';

    /**
     * @return list<Artbook>
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
     * @return list<Artbook>
     */
    private function fetchSearchResults(string $search): array
    {
        $sql = "
            SELECT *
            FROM {$this->table()}
            WHERE (
                artbook LIKE :search_artbook
                OR auteur LIKE :search_auteur
                OR serie LIKE :search_serie
                OR slug LIKE :search_slug
            )
            ORDER BY artbook ASC, numero ASC
        ";

        /** @var list<Artbook> $artbooks */
        $artbooks = $this->fetchAll(
            $sql,
            [
                'search_artbook' => "%{$search}%",
                'search_auteur' => "%{$search}%",
                'search_serie' => "%{$search}%",
                'search_slug' => '%' . $this->slugSearch($search) . '%',
            ],
            Artbook::class,
        );

        return $artbooks;
    }
}