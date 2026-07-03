<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\MangaData;
use App\DTO\Manga\Responses\MangaSearchData;
use App\DTO\Manga\Responses\MangaSearchItemData;
use App\DTO\Manga\Responses\MangaSeriesData;
use App\DTO\Manga\Responses\MangaSeriesItemData;
use App\DTO\Manga\Responses\MangaShowData;
use App\Models\Manga;
use App\Repositories\Manga\MangaRepository;
use App\Repositories\Manga\MangaCollectionRepository;
use App\Repositories\Manga\MangaSearchRepository;

use Framework\Application\App;

final readonly class MangaReadService
{
    public function __construct(
        private MangaRepository $mangaRepository,
        private MangaSearchRepository $searchRepository,
        private MangaCollectionRepository $collectionRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | SERIES
    |--------------------------------------------------------------------------
    */

    public function series(int|string $page = 1): ?MangaSeriesData
    {
        $page = max(1, (int) $page);

        $perPage = App::pagination();

        $totalSeries = $this->collectionRepository->countFirstTomes();

        if ($totalSeries === 0)
        {
            return null;
        }

        $totalPages = (int) ceil($totalSeries / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $mangas = $this->collectionRepository->findAllFirstTomes(
            'id DESC',
            $perPage,
            $page,
        );

        return new MangaSeriesData(
            mangas: array_map($this->mapSeriesItem(...), $mangas),
            currentPage: $page,
            slugFilter: null,
            totalSeries: $totalSeries,
            perPage: $perPage,
            totalPages: $totalPages,
        );
    }

    public function seriesExists(string $slug): bool
    {
        return $this->mangaRepository->seriesExists($slug);
    }

    public function showSeries(string $slug): ?MangaSeriesData
    {
        $mangas = $this->mangaRepository->findBySlug($slug);

        if ($mangas === [])
        {
            return null;
        }

        $totalItems = count($mangas);

        return new MangaSeriesData(
            mangas: array_map($this->mapSeriesItem(...), $mangas),
            currentPage: 1,
            slugFilter: $slug,
            totalSeries: $totalItems,
            perPage: $totalItems,
            totalPages: 1,
        );
    }

    public function one(string $slug, int $numero): ?MangaShowData
    {
        $manga = $this->mangaRepository->findOneBySlugAndNumero(
            $slug,
            $numero,
        );

        if ($manga === null)
        {
            return null;
        }

        return new MangaShowData(
            manga: $this->mapManga($manga),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): MangaSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query);

        return new MangaSearchData(
            results: array_map($this->mapSearchItem(...), $results),
            search: $query,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LISTS
    |--------------------------------------------------------------------------
    */

    /**
     * @return list<MangaSeriesItemData>
     */
    public function notes(): array
    {
        return array_map($this->mapSeriesItem(...), $this->collectionRepository->findSeriesWithoutPerfectNote());
    }

    /**
     * @return list<MangaSeriesItemData>
     */
    public function aLire(): array
    {
        return array_map($this->mapSeriesItem(...), $this->collectionRepository->findIncompleteSeries());
    }

    /*
    |--------------------------------------------------------------------------
    | MAPPERS
    |--------------------------------------------------------------------------
    */

    private function mapManga(Manga $manga): MangaData
    {
        $baseUri = App::baseUri();

        $thumbnail =
            $manga->thumbnail !== ''
                ? $manga->thumbnail
                : null;

        $extension =
            $manga->extension !== ''
                ? $manga->extension
                : null;

        $status =
            $manga->statut !== ''
                ? $manga->statut
                : 'en_cours';

        return new MangaData(
            id: $manga->id,
            slug: $manga->slug,
            livre: $manga->livre,

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/manga/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            editeur: $manga->editeur,

            hasEditeur:
                trim($manga->editeur) !== '',

            numero: $manga->numero,
            lu: $manga->lu,

            statut: $status,

            statusLabel:
                $status === 'termine'
                    ? 'Terminé'
                    : 'En cours',

            jacquette: $manga->jacquette,
            livreNote: $manga->livre_note,
            note: $manga->note,

            noteLabel: $manga->note !== null ? $manga->note . '/10' : 'Non calculée',

            commentaire: $manga->commentaire,

            hasCommentaire:
                $manga->commentaire !== null
                && trim($manga->commentaire) !== '',

            total: $manga->total,
            totalLu: $manga->total_lu,
            averageNote: $manga->average_note,

            isPerfectJacquette:
                $manga->jacquette === 5,

            isPerfectLivre:
                $manga->livre_note === 5,

            xpReadRewarded: $manga->xp_read_rewarded,
            xpSeriesRewarded: $manga->xp_series_rewarded,
        );
    }

    private function mapSeriesItem(Manga $manga): MangaSeriesItemData
    {
        $baseUri = App::baseUri();

        $thumbnail = $manga->thumbnail !== '' ? $manga->thumbnail : null;

        $extension = $manga->extension !== '' ? $manga->extension : null;

        $status = $manga->statut !== '' ? $manga->statut : 'en_cours';

        return new MangaSeriesItemData(
            slug: $manga->slug,
            numero: $manga->numero,
            livre: $manga->livre,

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/manga/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            statut: $status,

            statusLabel: $status === 'termine' ? 'Terminé' : 'En cours',

            statusClass: $status === 'termine' ? 'collection-status-finished' : 'collection-status-progress',

            note: $manga->note === null ? null : (float) $manga->note,

            averageNote: $manga->average_note,

            total: $manga->total ?? 0,
            totalLu: $manga->total_lu ?? 0,

            lu: $manga->lu,

            isFullyRead: ($manga->total ?? 0) > 0 && ($manga->total_lu ?? 0) >= ($manga->total ?? 0),
        );
    }

    private function mapSearchItem(Manga $manga): MangaSearchItemData
    {
        return new MangaSearchItemData(
            slug: $manga->slug,
            numero: $manga->numero,
            livre: $manga->livre,
            thumbnail: $manga->thumbnail !== '' ? $manga->thumbnail : null,
            extension: $manga->extension !== '' ? $manga->extension : null,
            note: $manga->note,
            lu: $manga->lu,
        );
    }
}