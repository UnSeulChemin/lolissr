<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\DTO\Nendoroid\Responses\NendoroidData;
use App\DTO\Nendoroid\Responses\NendoroidListData;
use App\DTO\Nendoroid\Responses\NendoroidListItemData;
use App\DTO\Nendoroid\Responses\NendoroidSearchData;
use App\DTO\Nendoroid\Responses\NendoroidSearchItemData;
use App\Models\Nendoroid\Nendoroid;
use App\Repositories\Nendoroid\NendoroidCollectionRepository;
use App\Repositories\Nendoroid\NendoroidRepository;
use App\Repositories\Nendoroid\NendoroidSearchRepository;
use App\Services\Collections\Concerns\BuildsCollectionReadData;

use Framework\Support\Dates\DateFormatter;

final readonly class NendoroidReadService
{
    use BuildsCollectionReadData;

    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private NendoroidCollectionRepository $collectionRepository,
        private NendoroidSearchRepository $searchRepository
    )
    {
    }

    // --------------------------------------------------------------------------
    // WAIFUS
    // --------------------------------------------------------------------------

    public function waifus(int|string $page = 1): ?NendoroidListData
    {
        $data = $this->collectionPage(
            $page,
            $this->collectionRepository->countAll(),
            $this->collectionRepository->findPaginated(...),
            $this->mapListItem(...)
        );
        if ($data === null) return null;

        return new NendoroidListData(
            nendoroids: $data['items'],
            currentPage: $data['currentPage'],
            totalWaifus: $data['totalItems'],
            perPage: $data['perPage'],
            totalPages: $data['totalPages']
        );
    }

    public function one(string $slug, int $numero): ?NendoroidData
    {
        $nendoroid = $this->nendoroidRepository->findOneBySlugAndNumero($slug, $numero);

        if ($nendoroid === null)
        {
            return null;
        }

        return $this->mapNendoroid($nendoroid);
    }

    // --------------------------------------------------------------------------
    // RECHERCHE
    // --------------------------------------------------------------------------

    public function search(string|int $query = '', int $limit = 20): NendoroidSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query, $limit);

        return new NendoroidSearchData(results: \App\Support\Media\ImageAssets::withFingerprints(fn (): array => array_map($this->mapSearchItem(...), $results)), search: $query);
    }

    // --------------------------------------------------------------------------
    // CONVERSIONS
    // --------------------------------------------------------------------------

    private function mapListItem(Nendoroid $nendoroid): NendoroidListItemData
    {
        $image = $this->collectionThumbnail('nendoroid', $nendoroid->thumbnail, $nendoroid->extension);

        return new NendoroidListItemData(
            slug: $nendoroid->slug,
            numero: $nendoroid->numero,

            waifu: $nendoroid->waifu,
            origin: $nendoroid->origin,

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

            collect: $nendoroid->collect
        );
    }

    private function mapNendoroid(Nendoroid $nendoroid): NendoroidData
    {
        $image = $this->collectionThumbnail('nendoroid', $nendoroid->thumbnail, $nendoroid->extension);

        return new NendoroidData(
            id: $nendoroid->id,

            slug: $nendoroid->slug,
            numero: $nendoroid->numero,

            waifu: $nendoroid->waifu,
            origin: $nendoroid->origin,
            company: $nendoroid->company,

            collect: $nendoroid->collect,

            release_date: DateFormatter::display($nendoroid->release_date),

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

            commentaire: $nendoroid->commentaire,

            xpCollectRewarded: $nendoroid->collect_rewarded
        );
    }

    private function mapSearchItem(Nendoroid $nendoroid): NendoroidSearchItemData
    {
        $image = $this->collectionThumbnail('nendoroid', $nendoroid->thumbnail, $nendoroid->extension, true);

        return new NendoroidSearchItemData(

            thumbnailUrl: $image['thumbnailUrl'],
            slug: $nendoroid->slug,
            numero: $nendoroid->numero,

            origin: $nendoroid->origin,
            waifu: $nendoroid->waifu,

            thumbnail: $image['thumbnail'],
            extension: $image['extension']
        );
    }
}
