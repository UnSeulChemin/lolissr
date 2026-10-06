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
            totalArtbooks: $data['totalItems'],
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
        $image = $this->collectionThumbnail('artbook', $artbook->thumbnail, $artbook->extension);

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

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

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
        $image = $this->collectionThumbnail('artbook', $artbook->thumbnail, $artbook->extension);

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

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

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
        $image = $this->collectionThumbnail('artbook', $artbook->thumbnail, $artbook->extension, true);

        $auteur = trim((string) $artbook->auteur) !== ''
            ? $artbook->auteur
            : null;

        $serie = trim((string) $artbook->serie) !== ''
            ? $artbook->serie
            : null;

        return new ArtbookSearchItemData(

            thumbnailUrl: $image['thumbnailUrl'],
            slug: $artbook->slug,
            numero: $artbook->numero,

            artbook: $artbook->artbook,
            auteur: $auteur,
            serie: $serie,

            thumbnail: $image['thumbnail'],
            extension: $image['extension']
        );
    }
}
