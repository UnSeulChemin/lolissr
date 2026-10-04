<?php

declare(strict_types=1);

namespace App\Repositories\Artbook;

use App\Models\Artbook;
use App\Repositories\AbstractRepository;

use Framework\Support\Str;

final class ArtbookSearchRepository extends AbstractRepository
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

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    private function normalizeSearch(string $search): string
    {
        return trim(preg_replace('/\s+/', ' ', trim($search)) ?? '');
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
        $slug = $this->slugSearch($search);

        $sql = "
            SELECT slug, numero, artbook, auteur, serie, thumbnail, extension, company
            FROM {$this->readTable()}
            WHERE (
                artbook LIKE :search_artbook
                OR auteur LIKE :search_auteur
                OR serie LIKE :search_serie
                OR slug LIKE :search_slug
            )
            ORDER BY artbook ASC, numero ASC, id ASC LIMIT 20
        ";

        /** @var list<Artbook> $artbooks */
        $artbooks = $this->fetchAll(
            $sql,
            [
                'search_artbook' => "%{$search}%",
                'search_auteur' => "%{$search}%",
                'search_serie' => "%{$search}%",
                'search_slug' => $slug !== '' ? '%' . $slug . '%' : null
            ],
            Artbook::class
        );

        return $artbooks;
    }
}