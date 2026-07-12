<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\ArtbookData;
use App\DTO\Manga\Responses\ArtbookSearchData;
use App\DTO\Manga\Responses\ArtbookSearchItemData;
use App\DTO\Manga\Responses\ArtbookSeriesData;
use App\DTO\Manga\Responses\ArtbookSeriesItemData;
use App\Models\Artbook;
use App\Repositories\Manga\ArtbookCollectionRepository;
use App\Repositories\Manga\ArtbookRepository;
use App\Repositories\Manga\ArtbookSearchRepository;
use App\Repositories\Manga\ArtbookStatsRepository;

use Framework\Application\App;
use Framework\Support\DateFormatter;

final readonly class ArtbookReadService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private ArtbookCollectionRepository $collectionRepository,
        private ArtbookSearchRepository $searchRepository,
        private ArtbookStatsRepository $statsRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | ARTBOOKS
    |--------------------------------------------------------------------------
    */

    public function artbooks(int|string $page = 1): ?ArtbookSeriesData
    {
        $page = max(1, (int) $page);

        $perPage = App::pagination();

        $totalArtbooks = $this->statsRepository->countAll();

        if ($totalArtbooks === 0)
        {
            return new ArtbookSeriesData(
                artbooks: [],
                currentPage: 1,
                totalArtbooks: 0,
                perPage: $perPage,
                totalPages: 1,
            );
        }

        $totalPages = (int) ceil($totalArtbooks / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $artbooks = $this->collectionRepository->findPaginated(
            $perPage,
            $page,
        );

        return new ArtbookSeriesData(
            artbooks: array_map(
                $this->mapSeriesItem(...),
                $artbooks
            ),
            currentPage: $page,
            totalArtbooks: $totalArtbooks,
            perPage: $perPage,
            totalPages: $totalPages,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function one(string $slug, int $numero): ?ArtbookData
    {
        $artbook = $this->artbookRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($artbook === null)
        {
            return null;
        }

        return $this->mapArtbook($artbook);
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): ArtbookSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query);

        return new ArtbookSearchData(
            results: array_map(
                $this->mapSearchItem(...),
                $results
            ),
            search: $query,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MAPPERS
    |--------------------------------------------------------------------------
    */

    private function mapArtbook(Artbook $artbook): ArtbookData
    {
        $baseUri = App::baseUri();

        $thumbnail = $artbook->thumbnail !== ''
            ? $artbook->thumbnail
            : null;

        $extension = $artbook->extension !== ''
            ? $artbook->extension
            : null;

        $auteur = trim((string) $artbook->auteur) !== ''
            ? $artbook->auteur
            : null;

        $serie = trim((string) $artbook->serie) !== ''
            ? $artbook->serie
            : null;

        $commentaire = trim((string) $artbook->commentaire) !== ''
            ? $artbook->commentaire
            : null;

        return new ArtbookData(
            id: $artbook->id,

            slug: $artbook->slug,
            numero: $artbook->numero,

            lu: $artbook->lu,

            artbook: $artbook->artbook,

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/artbook/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            auteur: $auteur,
            hasAuteur: $auteur !== null,

            serie: $serie,
            hasSerie: $serie !== null,

            company: $artbook->company,

            releaseDate: DateFormatter::display(
                $artbook->release_date,
            ),

            commentaire: $commentaire,
            hasCommentaire: $commentaire !== null,

            createdAt: $artbook->created_at,
        );
    }

    private function mapSeriesItem(Artbook $artbook): ArtbookSeriesItemData
    {
        $baseUri = App::baseUri();

        $thumbnail = $artbook->thumbnail !== ''
            ? $artbook->thumbnail
            : null;

        $extension = $artbook->extension !== ''
            ? $artbook->extension
            : null;

        $auteur = trim((string) $artbook->auteur) !== ''
            ? $artbook->auteur
            : null;

        $serie = trim((string) $artbook->serie) !== ''
            ? $artbook->serie
            : null;

        return new ArtbookSeriesItemData(
            slug: $artbook->slug,
            numero: $artbook->numero,

            artbook: $artbook->artbook,

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/artbook/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            auteur: $auteur,
            serie: $serie,

            subtitle:
                $serie
                ?? $auteur
                ?? 'Artbook',
        );
    }

    private function mapSearchItem(Artbook $artbook): ArtbookSearchItemData
    {
        $thumbnail = $artbook->thumbnail !== ''
            ? $artbook->thumbnail
            : null;

        $extension = $artbook->extension !== ''
            ? $artbook->extension
            : null;

        $auteur = trim((string) $artbook->auteur) !== ''
            ? $artbook->auteur
            : null;

        $serie = trim((string) $artbook->serie) !== ''
            ? $artbook->serie
            : null;

        return new ArtbookSearchItemData(
            slug: $artbook->slug,
            numero: $artbook->numero,

            artbook: $artbook->artbook,
            auteur: $auteur,
            serie: $serie,

            thumbnail: $thumbnail,
            extension: $extension,
        );
    }
}