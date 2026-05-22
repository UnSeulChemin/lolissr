<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\MangaSearchData;
use App\DTO\Manga\Responses\MangaSearchItemData;
use App\DTO\Manga\Responses\MangaSeriesData;
use App\DTO\Manga\Responses\MangaSeriesItemData;
use App\DTO\Manga\Responses\MangaShowData;
use App\Models\Manga;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaSearchRepository;
use Framework\Application\App;
use Framework\Support\Str;

/**
 * Service pour lire les données de mangas.
 */
final readonly class MangaReadService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaSearchRepository $searchRepository,
    ) {
    }

    /**
     * Nettoie et normalise la requête de recherche.
     */
    private function normalizeSearchQuery(string $query): string
    {
        $query = urldecode($query);
        return trim(preg_replace('/\s+/', ' ', $query) ?? '');
    }

    /**
     * Recherche les mangas correspondant à la requête.
     * 
     * @return Manga[]
     */
    private function findSearchResults(string $query): array
    {
        $search = $this->normalizeSearchQuery($query);
        if ($search === '') {
            return [];
        }

        return $this->searchRepository->searchMangas($search);
    }

    /**
     * Mappe un objet Manga en MangaSearchItemData.
     */
    private function mapSearchItem(Manga $manga): MangaSearchItemData
    {
        return new MangaSearchItemData(
            slug: $manga->slug,
            numero: $manga->numero,
            livre: $manga->livre,
            thumbnail: $manga->thumbnail !== '' ? $manga->thumbnail : null,
            extension: $manga->extension !== '' ? $manga->extension : null,
            note: $manga->note,
        );
    }

    /**
     * Mappe un objet Manga en MangaSeriesItemData.
     */
    private function mapSeriesItem(Manga $manga): MangaSeriesItemData
    {
        return new MangaSeriesItemData(
            slug: $manga->slug,
            numero: $manga->numero,
            livre: $manga->livre,
            thumbnail: $manga->thumbnail !== '' ? $manga->thumbnail : null,
            extension: $manga->extension !== '' ? $manga->extension : null,
            statut: $manga->statut !== '' ? $manga->statut : 'en_cours',
            note: $manga->note !== null ? (float) $manga->note : null,
            averageNote: $manga->average_note,
            total: $manga->total ?? 0,
            totalLu: $manga->total_lu ?? 0,
            lu: $manga->lu,
        );
    }

    /**
     * Retourne les mangas d’une page avec pagination.
     * 
     * @param string $page Numéro de page (doit être un entier positif)
     * @return MangaSeriesData|null
     */
    public function series(string $page = '1'): ?MangaSeriesData
    {
        if (!ctype_digit($page)) {
            return null;
        }

        $currentPage = (int)$page;
        if ($currentPage < 1) {
            return null;
        }

        $pagination = App::pagination();
        $totalPages = $this->searchRepository->countFirstTomesPaginate($pagination);

        if ($totalPages > 0 && $currentPage > $totalPages) {
            return null;
        }

        $mangas = $this->searchRepository->findAllFirstTomes('id DESC', $pagination, $currentPage);

        return new MangaSeriesData(
            mangas: array_map($this->mapSeriesItem(...), $mangas),
            compteur: $totalPages,
            slugFilter: null,
            currentPage: $currentPage,
        );
    }

    /**
     * Recherche les mangas selon une requête textuelle.
     */
    public function search(string $query = ''): MangaSearchData
    {
        $search = $this->normalizeSearchQuery($query);

        if ($search === '') {
            return new MangaSearchData(mangas: [], search: '');
        }

        return new MangaSearchData(
            mangas: array_map($this->mapSearchItem(...), $this->findSearchResults($search)),
            search: $search,
        );
    }

    /**
     * Retourne les 6 premiers résultats de recherche.
     */
    public function searchResults(string $query = ''): array
    {
        return array_slice(
            array_map($this->mapSearchItem(...), $this->findSearchResults($query)),
            0,
            6
        );
    }

    /**
     * Affiche une série complète selon le slug.
     */
    public function showSeries(string $slug): ?MangaSeriesData
    {
        $normalizedSlug = Str::slug($slug);
        $mangas = $this->mangaRepository->findBySlug($normalizedSlug);

        if (empty($mangas)) {
            return null;
        }

        return new MangaSeriesData(
            mangas: array_map($this->mapSeriesItem(...), $mangas),
            compteur: null,
            slugFilter: $normalizedSlug,
            currentPage: 1,
        );
    }

    /**
     * Retourne un seul manga avec le numéro précis.
     */
    public function one(string $slug, int $numero): ?MangaShowData
    {
        $normalizedSlug = Str::slug($slug);
        $manga = $this->mangaRepository->findOneBySlugAndNumero($normalizedSlug, $numero);

        if ($manga === null) {
            return null;
        }

        return new MangaShowData(
            manga: $manga,
            canonicalSlug: $normalizedSlug,
        );
    }
}