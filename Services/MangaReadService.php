<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Str;
use App\Models\MangaModel;

class MangaReadService
{
    public function __construct(
        private readonly MangaModel $mangaModel = new MangaModel()
    ) {
    }

    /**
     * Normalise une requête de recherche.
     */
    private function normalizeSearchQuery(string $query): string
    {
        return trim(str_replace('-', ' ', urldecode($query)));
    }

    /**
     * Retourne les données de collection paginée.
     *
     * @return array{
     *     mangas: array,
     *     compteur: int,
     *     currentPage: int
     * }|null
     */
    public function collection(string $page = '1'): ?array
    {
        if (!ctype_digit($page))
        {
            return null;
        }

        $currentPage = (int) $page;

        if ($currentPage < 1)
        {
            return null;
        }

        $pagination = App::pagination();
        $compteur = $this->mangaModel->countFirstTomesPaginate($pagination);

        if ($compteur > 0 && $currentPage > $compteur)
        {
            return null;
        }

        return [
            'mangas' => $this->mangaModel->findAllFirstTomes('id DESC', $pagination, $currentPage),
            'compteur' => $compteur,
            'currentPage' => $currentPage
        ];
    }

    /**
     * Retourne les données de recherche.
     *
     * @return array{
     *     mangas: array,
     *     search: string
     * }
     */
    public function search(string $query = ''): array
    {
        $search = $this->normalizeSearchQuery($query);

        if ($search === '')
        {
            return [
                'mangas' => [],
                'search' => ''
            ];
        }

        return [
            'mangas' => $this->mangaModel->searchMangas($search),
            'search' => $search
        ];
    }

    /**
     * Retourne les résultats live search.
     */
    public function searchAjax(string $query = ''): array
    {
        $search = $this->normalizeSearchQuery($query);

        if ($search === '')
        {
            return [];
        }

        $mangas = $this->mangaModel->searchMangas($search);
        $results = [];

        foreach (array_slice($mangas, 0, 6) as $manga)
        {
            $results[] = [
                'slug' => $manga->slug,
                'numero' => (int) $manga->numero,
                'livre' => $manga->livre,
                'thumbnail' => $manga->thumbnail,
                'extension' => $manga->extension,
                'note' => $manga->note
            ];
        }

        return $results;
    }

    /**
     * Retourne les mangas d’une série avec slug canonique.
     *
     * @return array{
     *     mangas: array,
     *     canonicalSlug: string
     * }|null
     */
    public function serie(string $slug): ?array
    {
        $normalizedSlug = Str::slug($slug);
        $mangas = $this->mangaModel->findBySlug($normalizedSlug);

        if (!$mangas)
        {
            return null;
        }

        return [
            'mangas' => $mangas,
            'canonicalSlug' => Str::slug((string) $mangas[0]->slug)
        ];
    }

    /**
     * Retourne un manga avec slug canonique.
     *
     * @return array{
     *     manga: object,
     *     canonicalSlug: string
     * }|null
     */
    public function one(string $slug, int $numero): ?array
    {
        $normalizedSlug = Str::slug($slug);
        $manga = $this->mangaModel->findOneBySlugAndNumero($normalizedSlug, $numero);

        if (!$manga)
        {
            return null;
        }

        return [
            'manga' => $manga,
            'canonicalSlug' => Str::slug((string) $manga->slug)
        ];
    }
}