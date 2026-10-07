<?php

declare(strict_types=1);

namespace App\Repositories\Manga;

use App\Models\Manga\Manga;
use App\Repositories\AbstractRepository;

use Framework\Support\Strings;

final class MangaSearchRepository extends AbstractRepository
{
    protected string $table = 'manga';

    /**
     * @return list<Manga>
     */
    public function search(string $search, int $limit = 20): array
    {
        $limit = max(1, min(20, $limit));
        $search = $this->normalizeSearch($search);

        if ($search === '')
        {
            return [];
        }

        $searchNumero = $this->extractSearchNumero($search);

        if ($searchNumero !== null)
        {
            // Un nombre peut faire partie du titre, par exemple Kaiju No. 8.
            $titleMatches = $this->fetchSearchResults($search, exactTitle: true, limit: $limit);
            if ($titleMatches !== [])
            {
                return $titleMatches;
            }

            return $this->fetchSearchResults($searchNumero['title'], $searchNumero['numero'], limit: $limit);
        }

        return $this->fetchSearchResults($search, limit: $limit);
    }

    // --------------------------------------------------------------------------
    // UTILITAIRES
    // --------------------------------------------------------------------------

    private function normalizeSearch(string $search): string
    {
        return trim(preg_replace('/\s+/', ' ', \App\Support\Search\SearchQuery::validate($search)) ?? '');
    }

    private function slugSearch(string $search): string
    {
        return Strings::slug($search);
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

        return ['title' => $title, 'numero' => $numero];
    }

    /**
     * @return list<Manga>
     */
    private function fetchSearchResults(string $title, ?int $numero = null, bool $exactTitle = false, int $limit = 20): array
    {
        $operator = $exactTitle ? '=' : 'LIKE';
        $escape = $exactTitle ? '' : " ESCAPE '!'";
        $sql = "SELECT slug, numero, livre, thumbnail, extension, note, lu FROM {$this->readTable()} WHERE (livre {$operator} :search_livre{$escape} OR slug {$operator} :search_slug{$escape})";

        $slug = $this->slugSearch($title);
        $params = [
            'search_livre' => $exactTitle ? $title : \App\Support\Search\SearchQuery::containsPattern($title),
            'search_slug' => $slug !== '' ? ($exactTitle ? $slug : \App\Support\Search\SearchQuery::containsPattern($slug)) : null
        ];

        if ($numero !== null)
        {
            $sql .= ' AND numero = :numero';

            $params['numero'] = $numero;
        }

        $sql .= " ORDER BY livre ASC, numero ASC, id ASC LIMIT {$limit}";

        /** @var list<Manga> $mangas */
        $mangas = $this->fetchAll($sql, $params, Manga::class);

        return $mangas;
    }
}
