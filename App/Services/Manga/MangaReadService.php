<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\MangaSearchData;
use App\DTO\Manga\Responses\MangaSearchItemData;
use App\DTO\Manga\Responses\MangaSeriesData;
use App\DTO\Manga\Responses\MangaSeriesItemData;
use App\DTO\Manga\Responses\MangaShowData;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaSearchRepository;
use Framework\Application\App;
use Framework\Support\Str;

final readonly class MangaReadService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaSearchRepository $searchRepository,
    ) {
    }

    private function normalizeSearchQuery(
        string $query,
    ): string {
        return trim(
            preg_replace(
                '/\s+/',
                ' ',
                $query,
            ) ?? '',
        );
    }

    /**
     * @return list<object>
     */
    private function findSearchResults(
        string $query,
    ): array {
        $search = $this->normalizeSearchQuery(
            $query,
        );

        if ($search === '') {
            return [];
        }

        return $this->searchRepository
            ->searchMangas($search);
    }

    private function mapSearchItem(
        object $manga,
    ): MangaSearchItemData {
        return new MangaSearchItemData(
            slug: (string) $manga->slug,
            numero: (int) $manga->numero,
            livre: (string) $manga->livre,
            thumbnail: isset($manga->thumbnail)
                ? (string) $manga->thumbnail
                : null,
            extension: isset($manga->extension)
                ? (string) $manga->extension
                : null,
            note: isset($manga->note)
                ? (int) $manga->note
                : null,
        );
    }

    private function mapSeriesItem(
        object $manga,
    ): MangaSeriesItemData {
        return new MangaSeriesItemData(
            slug: (string) $manga->slug,
            numero: (int) $manga->numero,
            livre: (string) $manga->livre,

            thumbnail: isset($manga->thumbnail)
                ? (string) $manga->thumbnail
                : null,

            extension: isset($manga->extension)
                ? (string) $manga->extension
                : null,

            statut: isset($manga->statut)
                ? (string) $manga->statut
                : 'en_cours',

            note: isset($manga->note)
                ? (float) $manga->note
                : null,

            averageNote: isset($manga->average_note)
                ? (float) $manga->average_note
                : null,

            total: (int) (
                $manga->total
                ?? 0
            ),

            totalLu: (int) (
                $manga->total_lu
                ?? 0
            ),

            lu: (int) (
                $manga->lu
                ?? 0
            ),
        );
    }

    public function series(
        string $page = '1',
    ): ?MangaSeriesData {
        if (!ctype_digit($page)) {
            return null;
        }

        $currentPage = (int) $page;

        if ($currentPage < 1) {
            return null;
        }

        $pagination = App::pagination();

        $totalPages = $this->searchRepository
            ->countFirstTomesPaginate(
                $pagination,
            );

        if (
            $totalPages > 0
            && $currentPage > $totalPages
        ) {
            return null;
        }

        $mangas = $this->searchRepository
            ->findAllFirstTomes(
                'id DESC',
                $pagination,
                $currentPage,
            );

        return new MangaSeriesData(
            mangas: array_map(
                $this->mapSeriesItem(...),
                $mangas,
            ),
            compteur: $totalPages,
            slugFilter: null,
            currentPage: $currentPage,
        );
    }

    public function search(
        string $query = '',
    ): MangaSearchData {
        $search = $this->normalizeSearchQuery(
            $query,
        );

        if ($search === '') {
            return new MangaSearchData(
                mangas: [],
                search: '',
            );
        }

        return new MangaSearchData(
            mangas: array_map(
                $this->mapSearchItem(...),
                $this->findSearchResults($search),
            ),
            search: $search,
        );
    }

    /**
     * @return list<MangaSearchItemData>
     */
    public function searchAjax(
        string $query = '',
    ): array {
        return array_slice(
            array_map(
                $this->mapSearchItem(...),
                $this->findSearchResults($query),
            ),
            0,
            6,
        );
    }

    public function serie(
        string $slug,
    ): ?MangaSeriesData {
        $normalizedSlug = Str::slug($slug);

        $mangas = $this->mangaRepository
            ->findBySlug($normalizedSlug);

        if ($mangas === []) {
            return null;
        }

        return new MangaSeriesData(
            mangas: array_map(
                $this->mapSeriesItem(...),
                $mangas,
            ),
            compteur: null,
            slugFilter: $normalizedSlug,
            currentPage: 1,
        );
    }

    public function one(
        string $slug,
        int $numero,
    ): ?MangaShowData {
        $normalizedSlug = Str::slug($slug);

        $manga = $this->mangaRepository
            ->findOneBySlugAndNumero(
                $normalizedSlug,
                $numero,
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