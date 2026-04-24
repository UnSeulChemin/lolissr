<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application\App;
use App\Core\Support\Str;
use App\Repositories\MangaRepository;

final class MangaReadService
{
    public function __construct(
        private readonly MangaRepository $mangaRepository = new MangaRepository()
    ) {}

    /**
     * Retourne le repository manga.
     */
    public function repository(): MangaRepository
    {
        return $this->mangaRepository;
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    private function normalizeSearchQuery(
        string $query
    ): string {

        $query = urldecode($query);

        $query = str_replace('-', ' ', $query);

        $query = trim(
            preg_replace('/\s+/', ' ', $query) ?? ''
        );

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | COLLECTION
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     mangas: array<int, object>,
     *     compteur: int,
     *     currentPage: int
     * }|null
     */
    public function collection(
        string $page = '1'
    ): ?array {

        if (!ctype_digit($page))
        {
            return null;
        }

        $currentPage = (int) $page;

        if ($currentPage < 1)
        {
            return null;
        }

        $pagination =
            App::pagination();

        $compteur =
            $this->mangaRepository
                ->countFirstTomesPaginate(
                    $pagination
                );

        if ($currentPage > $compteur)
        {
            return null;
        }

        $mangas =
            $this->mangaRepository
                ->findAllFirstTomes(
                    'id DESC',
                    $pagination,
                    $currentPage
                );

        return [
            'mangas' => $mangas,
            'compteur' => $compteur,
            'currentPage' => $currentPage
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH PAGE
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     mangas: array<int, object>,
     *     search: string
     * }
     */
    public function search(
        string $query = ''
    ): array {

        $search =
            $this->normalizeSearchQuery(
                $query
            );

        if ($search === '')
        {
            return [
                'mangas' => [],
                'search' => ''
            ];
        }

        $mangas =
            $this->mangaRepository
                ->searchMangas($search);

        return [
            'mangas' => $mangas,
            'search' => $search
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX SEARCH
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchAjax(
        string $query = ''
    ): array {

        $search =
            $this->normalizeSearchQuery(
                $query
            );

        if ($search === '')
        {
            return [];
        }

        $mangas =
            $this->mangaRepository
                ->searchMangas($search);

        $results = [];

        foreach (
            array_slice($mangas, 0, 6)
            as $manga
        ) {

            if (!isset($manga->slug))
            {
                continue;
            }

            $results[] = [
                'slug' => $manga->slug,
                'numero' => (int) $manga->numero,
                'livre' => $manga->livre,
                'thumbnail' => $manga->thumbnail,
                'extension' => $manga->extension,
                'note' =>
                    $manga->note !== null
                        ? (int) $manga->note
                        : null
            ];
        }

        return $results;
    }

    /*
    |--------------------------------------------------------------------------
    | SERIE
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     mangas: array<int, object>,
     *     canonicalSlug: string
     * }|null
     */
    public function serie(
        string $slug
    ): ?array {

        $normalizedSlug =
            Str::slug($slug);

        $mangas =
            $this->mangaRepository
                ->findBySlug(
                    $normalizedSlug
                );

        if ($mangas === [])
        {
            return null;
        }

        return [
            'mangas' => $mangas,
            'canonicalSlug' => $normalizedSlug
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ONE
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{
     *     manga: object,
     *     canonicalSlug: string
     * }|null
     */
    public function one(
        string $slug,
        int $numero
    ): ?array {

        $normalizedSlug =
            Str::slug($slug);

        $manga =
            $this->mangaRepository
                ->findOneBySlugAndNumero(
                    $normalizedSlug,
                    $numero
                );

        if ($manga === false)
        {
            return null;
        }

        return [
            'manga' => $manga,
            'canonicalSlug' => $normalizedSlug
        ];
    }
}