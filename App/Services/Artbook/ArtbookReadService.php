<?php

declare(strict_types=1);

namespace App\Services\Artbook;

use App\DTO\Artbook\Responses\ArtbookData;
use App\DTO\Artbook\Responses\ArtbookListData;
use App\DTO\Artbook\Responses\ArtbookListItemData;
use App\DTO\Artbook\Responses\ArtbookSearchData;
use App\DTO\Artbook\Responses\ArtbookSearchItemData;
use App\Models\Artbook\Artbook;
use App\Repositories\Artbook\ArtbookCollectionRepository;
use App\Repositories\Artbook\ArtbookRepository;
use App\Repositories\Artbook\ArtbookSearchRepository;
use App\Repositories\Artbook\ArtbookStatsRepository;
use App\Services\Collections\Concerns\BuildsCollectionReadData;

use Framework\Config\ApplicationConfig;
use Framework\Support\Dates\DateFormatter;

final readonly class ArtbookReadService
{
    use BuildsCollectionReadData;

    public function __construct(
        private ArtbookRepository $artbookRepository,
        private ArtbookCollectionRepository $collectionRepository,
        private ArtbookSearchRepository $searchRepository,
        private ArtbookStatsRepository $statsRepository
    )
    {
    }

    // --------------------------------------------------------------------------
    // ARTBOOKS
    // --------------------------------------------------------------------------

    public function artbooks(int|string $page = 1): ?ArtbookListData
    {
        $data = $this->collectionPage(
            $page,
            $this->statsRepository->countAll(),
            $this->collectionRepository->findPaginated(...),
            $this->mapSeriesItem(...)
        );
        if ($data === null) return null;

        return new ArtbookListData(
            artbooks: $data['items'],
            currentPage: $data['currentPage'],
            totalArtbooks: $data['totalWaifus'],
            perPage: $data['perPage'],
            totalPages: $data['totalPages']
        );
    }

    // --------------------------------------------------------------------------
    // AFFICHAGE
    // --------------------------------------------------------------------------

    public function one(string $slug, int $numero): ?ArtbookData
    {
        $artbook = $this->artbookRepository->findOneBySlugAndNumero($slug, $numero);

        if ($artbook === null)
        {
            return null;
        }

        return $this->mapArtbook($artbook);
    }

    // --------------------------------------------------------------------------
    // RECHERCHE
    // --------------------------------------------------------------------------

    public function search(string|int $query = '', int $limit = 20): ArtbookSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query, $limit);

        return new ArtbookSearchData(results: \App\Support\Media\ImageAssets::withFingerprints(fn (): array => array_map($this->mapSearchItem(...), $results)), search: $query);
    }

    // --------------------------------------------------------------------------
    // CONVERSIONS
    // --------------------------------------------------------------------------

    private function mapArtbook(Artbook $artbook): ArtbookData
    {
        $baseUri = ApplicationConfig::baseUri();

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

            releaseDate: DateFormatter::display($artbook->release_date),

            commentaire: $commentaire,
            hasCommentaire: $commentaire !== null,

            createdAt: $artbook->created_at
        );
    }

    private function mapSeriesItem(Artbook $artbook): ArtbookListItemData
    {
        $baseUri = ApplicationConfig::baseUri();

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

        return new ArtbookListItemData(
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
                ?? 'Artbook'
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

            thumbnailUrl: $artbook->thumbnail !== '' && $artbook->extension !== '' ? \App\Support\Media\ImageAssets::url(view_base_uri() . 'images/artbook/thumbnail/' . $artbook->thumbnail . '.' . $artbook->extension, true) : null,
            slug: $artbook->slug,
            numero: $artbook->numero,

            artbook: $artbook->artbook,
            auteur: $auteur,
            serie: $serie,

            thumbnail: $thumbnail,
            extension: $extension
        );
    }
}
