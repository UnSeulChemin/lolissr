<?php

declare(strict_types=1);

namespace App\Services\Manga;

use App\DTO\Manga\Responses\ArtbookData;
use App\DTO\Manga\Responses\ArtbookSeriesData;
use App\DTO\Manga\Responses\ArtbookSeriesItemData;
use App\Models\Artbook;
use App\Repositories\Manga\ArtbookCollectionRepository;
use App\Repositories\Manga\ArtbookRepository;
use App\Repositories\Manga\ArtbookStatsRepository;

use Framework\Application\App;

final readonly class ArtbookReadService
{
    public function __construct(
        private ArtbookRepository $artbookRepository,
        private ArtbookCollectionRepository $collectionRepository,
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
            return null;
        }

        $totalPages = (int) ceil($totalArtbooks / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $artbooks = $this->collectionRepository->findPaginated($perPage, $page);

        return new ArtbookSeriesData(
            artbooks: array_map($this->mapSeriesItem(...), $artbooks),
            compteur: $totalPages,
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
        $artbook = $this->artbookRepository->findOneBySlugAndNumero($slug, $numero);

        if ($artbook === null)
        {
            return null;
        }

        return $this->mapArtbook($artbook);
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

        return new ArtbookData(
            id: $artbook->id,

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
            hasAuteur: $auteur !== null,

            serie: $serie,
            hasSerie: $serie !== null,

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

        $auteur =
            trim((string) $artbook->auteur) !== ''
                ? $artbook->auteur
                : null;

        $serie =
            trim((string) $artbook->serie) !== ''
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
}
