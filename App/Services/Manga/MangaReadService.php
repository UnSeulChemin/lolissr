<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\MangaSearchData;
use App\DTO\Manga\Responses\MangaSearchItemData;
use App\DTO\Manga\Responses\MangaSeriesData;
use App\DTO\Manga\Responses\MangaSeriesItemData;
use App\DTO\Manga\Responses\MangaShowData;
use App\Models\Manga;
use App\Models\Artbook;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaSearchRepository;
use App\Repositories\Manga\ArtbookRepository;

use Framework\Application\App;

final readonly class MangaReadService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaSearchRepository $searchRepository,
        private ArtbookRepository $artbookRepository,
    ) {
    }

    private function mapSeriesItem(
        Manga $manga,
    ): MangaSeriesItemData {

        return new MangaSeriesItemData(
            slug:
                $manga->slug,
            numero:
                $manga->numero,
            livre:
                $manga->livre,
            thumbnail:
                $manga->thumbnail !== ''
                    ? $manga->thumbnail
                    : null,
            extension:
                $manga->extension !== ''
                    ? $manga->extension
                    : null,
            statut:
                $manga->statut !== ''
                    ? $manga->statut
                    : 'en_cours',
            note:
                (float) $manga->note,
            averageNote:
                $manga->average_note,
            total:
                $manga->total ?? 0,
            totalLu:
                $manga->total_lu ?? 0,
            lu:
                $manga->lu,
        );
    }

    private function mapSearchItem(
        Manga $manga,
    ): MangaSearchItemData {

        return new MangaSearchItemData(
            slug:
                $manga->slug,
            numero:
                $manga->numero,
            livre:
                $manga->livre,
            thumbnail:
                $manga->thumbnail !== ''
                    ? $manga->thumbnail
                    : null,
            extension:
                $manga->extension !== ''
                    ? $manga->extension
                    : null,
            note:
                $manga->note,
            lu:
                $manga->lu,
        );
    }

    /**
     * Récupère les mangas pour la page demandée
     */
    public function series(
        int|string $page = 1,
    ): ?MangaSeriesData {

        $page = max(
            1,
            (int) $page,
        );

        $perPage =
            App::pagination();

        $totalSeries =
            $this->searchRepository
                ->countFirstTomes();

        if ($totalSeries === 0)
        {
            return null;
        }

        $totalPages =
            (int) ceil(
                $totalSeries / $perPage,
            );

        if ($page > $totalPages)
        {
            return null;
        }

        $mangas =
            $this->searchRepository
                ->findAllFirstTomes(
                    'id DESC',
                    $perPage,
                    $page,
                );

        return new MangaSeriesData(
            mangas:
                array_map(
                    $this->mapSeriesItem(...),
                    $mangas,
                ),
            compteur:
                $totalPages,
            currentPage:
                $page,
            slugFilter:
                null,
            totalSeries:
                $totalSeries,
            perPage:
                $perPage,
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

        $query =
            trim($query);

        $results =
            $this->searchRepository
                ->searchMangas(
                    $query,
                );

        return new MangaSearchData(
            results:
                array_map(
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

        $mangas =
            $this->mangaRepository
                ->findBySlug($slug);

        if ($mangas === [])
        {
            return null;
        }

        $totalItems =
            count($mangas);

        return new MangaSeriesData(
            mangas:
                array_map(
                    $this->mapSeriesItem(...),
                    $mangas,
                ),
            compteur: 1,
            currentPage: 1,
            slugFilter: $slug,
            totalSeries: $totalItems,
            perPage: $totalItems,
        );
    }

    public function one(
        string $slug,
        int $numero,
    ): ?MangaShowData {

        $manga =
            $this->mangaRepository
                ->findOneDtoBySlugAndNumero(
                    $slug,
                    $numero,
                );

        if ($manga === null)
        {
            return null;
        }

        return new MangaShowData(
            manga: $manga,
            canonicalSlug: $manga->slug,
        );
    }

    /**
     * @return list<MangaSeriesItemData>
     */
    public function notes(): array
    {
        return array_map(
            $this->mapSeriesItem(...),
            $this->mangaRepository
                ->findSeriesWithoutPerfectNote(),
        );
    }

    /**
     * @return list<MangaSeriesItemData>
     */
    public function aLire(): array
    {
        return array_map(
            $this->mapSeriesItem(...),
            $this->mangaRepository
                ->findIncompleteSeries(),
        );
    }

    /**
     * @return list<Artbook>
     */
    public function artbooks(): array
    {
        return $this->artbookRepository
            ->findAll();
    }

    public function oneArtbook(
        string $slug,
        int $numero,
    ): ?Artbook
    {
        return $this->artbookRepository
            ->findOneBySlugAndNumero(
                $slug,
                $numero,
            );
    }
}
