<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\Core\Application\App;
use App\Core\Support\Str;
use App\DTO\Manga\Responses\MangaSearchData;
use App\DTO\Manga\Responses\MangaSeriesData;
use App\DTO\Manga\Responses\MangaShowData;
use App\Repositories\Manga\MangaRepository;

final class MangaReadService
{
    public function __construct(
        private readonly MangaRepository $mangaRepository
    ) {}

    public function repository(): MangaRepository
    {
        return $this->mangaRepository;
    }

    private function normalizeSearchQuery(
        string $query
    ): string {
        $query = urldecode($query);

        $query = str_replace(
            '-',
            ' ',
            $query
        );

        return trim(
            preg_replace('/\s+/', ' ', $query)
            ?? ''
        );
    }

    public function series(
        string $page = '1'
    ): ?MangaSeriesData {
        if (!ctype_digit($page)) {
            return null;
        }

        $currentPage = (int) $page;

        if ($currentPage < 1) {
            return null;
        }

        $pagination = App::pagination();

        $compteur = $this->mangaRepository
            ->countFirstTomesPaginate(
                $pagination
            );

        if (
            $compteur > 0
            && $currentPage > $compteur
        ) {
            return null;
        }

        $mangas = $this->mangaRepository
            ->findAllFirstTomes(
                'id DESC',
                $pagination,
                $currentPage
            );

        return new MangaSeriesData(
            mangas: $mangas,
            compteur: $compteur,
            slugFilter: null,
            currentPage: $currentPage,
        );
    }

    public function search(
        string $query = ''
    ): MangaSearchData {
        $search = $this->normalizeSearchQuery(
            $query
        );

        if ($search === '') {
            return new MangaSearchData(
                mangas: [],
                search: '',
            );
        }

        $mangas = $this->mangaRepository
            ->searchMangas($search);

        return new MangaSearchData(
            mangas: $mangas,
            search: $search,
        );
    }

    /**
     * @return list<array{
     *     slug: string,
     *     numero: int,
     *     livre: string,
     *     thumbnail: string|null,
     *     extension: string|null,
     *     note: int|null
     * }>
     */
    public function searchAjax(
        string $query = ''
    ): array {
        $search = $this->normalizeSearchQuery(
            $query
        );

        if ($search === '') {
            return [];
        }

        $mangas = $this->mangaRepository
            ->searchMangas($search);

        $results = [];

        foreach (
            array_slice($mangas, 0, 6)
            as $manga
        ) {
            if (!isset($manga->slug)) {
                continue;
            }

            $results[] = [
                'slug' => $manga->slug,
                'numero' => (int) $manga->numero,
                'livre' => $manga->livre,
                'thumbnail' => $manga->thumbnail,
                'extension' => $manga->extension,
                'note' => $manga->note !== null
                    ? (int) $manga->note
                    : null,
            ];
        }

        return $results;
    }

    public function serie(
        string $slug
    ): ?MangaSeriesData {
        $normalizedSlug = Str::slug($slug);

        $mangas = $this->mangaRepository
            ->findBySlug($normalizedSlug);

        if ($mangas === []) {
            return null;
        }

        return new MangaSeriesData(
            mangas: $mangas,
            compteur: null,
            slugFilter: $normalizedSlug,
            currentPage: 1,
        );
    }

    public function one(
        string $slug,
        int $numero
    ): ?MangaShowData {
        $normalizedSlug = Str::slug($slug);

        $manga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $normalizedSlug,
                $numero
            );

        if ($manga === null) {
            return null;
        }

        return new MangaShowData(
            manga: $manga,
            canonicalSlug: $normalizedSlug,
        );
    }
}