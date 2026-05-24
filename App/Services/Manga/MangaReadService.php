<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\MangaSeriesData;
use App\DTO\Manga\Responses\MangaSeriesItemData;
use App\DTO\Manga\Responses\MangaSearchData;
use App\DTO\Manga\Responses\MangaSearchItemData;
use App\DTO\Manga\Responses\MangaShowData;
use App\Models\Manga;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaSearchRepository;
use Framework\Application\App;
use Framework\Support\Str;

final readonly class MangaReadService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaSearchRepository $searchRepository,
    ) {}

    private function mapSeriesItem(Manga $manga): MangaSeriesItemData
    {
        return new MangaSeriesItemData(
            slug: $manga->slug,
            numero: $manga->numero,
            livre: $manga->livre,
            thumbnail: $manga->thumbnail ?: null,
            extension: $manga->extension ?: null,
            statut: $manga->statut ?: 'en_cours',
            note: $manga->note !== null ? (float)$manga->note : null,
            averageNote: $manga->average_note ?? null,
            total: $manga->total ?? 0,
            totalLu: $manga->total_lu ?? 0,
            lu: $manga->lu ?? 0,
        );
    }

    private function mapSearchItem(
        Manga $manga,
    ): MangaSearchItemData {
        return new MangaSearchItemData(
            slug: $manga->slug,
            numero: $manga->numero,
            livre: $manga->livre,
            thumbnail: $manga->thumbnail ?: null,
            extension: $manga->extension ?: null,
            note: $manga->note,
        );
    }

    /**
     * Récupère les mangas pour la page demandée
     */
    public function series(int|string $page = 1): ?MangaSeriesData
    {
        $page = max(1, (int)$page);
        $perPage = App::pagination();

        $totalSeries = $this->searchRepository->countFirstTomes();
        if ($totalSeries === 0) return null;

        $totalPages = (int) ceil($totalSeries / $perPage);
        if ($page > $totalPages) $page = $totalPages;

        $mangas = $this->searchRepository->findAllFirstTomes('id DESC', $perPage, $page);

        return new MangaSeriesData(
            mangas: array_map($this->mapSeriesItem(...), $mangas),
            compteur: $totalPages,
            currentPage: $page,
            slugFilter: null,
            totalSeries: $totalSeries,
            perPage: $perPage,
        );
    }

    public function seriesExists(
        string $slug,
    ): bool {
        return $this->mangaRepository
            ->seriesExists($slug);
    }

    public function search(
        string $query = '',
    ): MangaSearchData {

        $results =
            $this->searchRepository
                ->searchMangas(
                    trim($query),
                );

        return new MangaSearchData(
            mangas: array_map(
                $this->mapSearchItem(...),
                $results,
            ),

            search:
                $query,
        );
    }

    public function showSeries(
        string $slug,
    ): ?MangaSeriesData {

        $normalizedSlug =
            Str::slug($slug);

        $mangas =
            $this->mangaRepository
                ->findBySlug(
                    $normalizedSlug,
                );

        if (empty($mangas)) {
            return null;
        }

        $totalItems =
            count($mangas);

        return new MangaSeriesData(
            mangas: array_map(
                $this->mapSeriesItem(...),
                $mangas,
            ),

            compteur: 1,

            currentPage: 1,

            slugFilter:
                $normalizedSlug,

            totalSeries:
                $totalItems,

            perPage:
                $totalItems,
        );
    }

    public function one(
        string $slug,
        int $numero,
    ): ?MangaShowData {

        $normalizedSlug =
            Str::slug($slug);

        $manga =
            $this->mangaRepository
                ->findOneBySlugAndNumero(
                    $normalizedSlug,
                    $numero,
                );

        if ($manga === null) {
            return null;
        }

        return new MangaShowData(
            manga:
                $manga,

            canonicalSlug:
                $normalizedSlug,
        );
    }
}