<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga;
use App\Models\Model;

use Framework\Support\Str;

final class MangaSearchRepository extends Model
{
    protected string $table = 'manga';

    /**
     * @return list<Manga>
     */
    public function search(string $search): array
    {
        $search = $this->normalizeSearch($search);

        if ($search === '')
        {
            return [];
        }

        $searchNumero = $this->extractSearchNumero($search);

        if ($searchNumero !== null)
        {
            return $this->fetchSearchResults($searchNumero['title'], $searchNumero['numero']);
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
        return trim(preg_replace('/\s+/', ' ', trim($search)) ?? '');
    }

    private function slugSearch(string $search): string
    {
        return Str::slug($search);
    }

    /**
     * @return array{
     *     title: string,
     *     numero: int
     * }|null
     */
    private function extractSearchNumero(string $search): ?array
    {
        $pattern = '/^(.*?)\s*(?:t|tome|vol(?:\.)?|volume|n°|no|\#)?\s*0*([1-9][0-9]*)$/iu';

        $matched = preg_match($pattern, $search, $matches);

        if ($matched !== 1)
        {
            return null;
        }

        $title = trim($matches[1]);
        $numero = (int) $matches[2];

        if ($title === '' || $numero < 1)
        {
            return null;
        }

        return [
            'title' => $title,
            'numero' => $numero,
        ];
    }

    /**
     * @return list<Manga>
     */
    private function fetchSearchResults(string $title, ?int $numero = null): array
    {
        $sql = "SELECT * FROM {$this->table()} WHERE (livre LIKE :search_livre OR slug LIKE :search_slug)";

        $params = ['search_livre' => "%{$title}%", 'search_slug' => '%' . $this->slugSearch($title) . '%'];

        if ($numero !== null)
        {
            $sql .= ' AND numero = :numero';

            $params['numero'] = $numero;
        }

        $sql .= ' ORDER BY livre ASC, numero ASC';

        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll($sql, $params, Manga::class);

        return $mangas;
    }
}
