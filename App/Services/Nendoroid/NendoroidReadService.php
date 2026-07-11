<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\DTO\Nendoroid\Responses\NendoroidData;
use App\DTO\Nendoroid\Responses\NendoroidListData;
use App\DTO\Nendoroid\Responses\NendoroidListItemData;
use App\DTO\Nendoroid\Responses\NendoroidSearchData;
use App\DTO\Nendoroid\Responses\NendoroidSearchItemData;
use App\Models\Nendoroid;
use App\Repositories\Nendoroid\NendoroidCollectionRepository;
use App\Repositories\Nendoroid\NendoroidRepository;
use App\Repositories\Nendoroid\NendoroidSearchRepository;

use Framework\Application\App;
use Framework\Support\DateFormatter;

final readonly class NendoroidReadService
{
    public function __construct(
        private NendoroidRepository $nendoroidRepository,
        private NendoroidCollectionRepository $collectionRepository,
        private NendoroidSearchRepository $searchRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | WAIFUS
    |--------------------------------------------------------------------------
    */

    public function waifus(int|string $page = 1): ?NendoroidListData
    {
        $page = max(1, (int) $page);

        $perPage = App::pagination();

        $totalWaifus = $this->collectionRepository->countAll();

        if ($totalWaifus === 0)
        {
            return null;
        }

        $totalPages = (int) ceil($totalWaifus / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $nendoroids = $this->collectionRepository->findPaginated(
            $perPage,
            $page,
        );

        return new NendoroidListData(
            nendoroids: array_map(
                $this->mapListItem(...),
                $nendoroids
            ),
            currentPage: $page,
            totalWaifus: $totalWaifus,
            perPage: $perPage,
            totalPages: $totalPages,
        );
    }

    public function one(string $slug, int $numero): ?NendoroidData
    {
        $nendoroid = $this->nendoroidRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($nendoroid === null)
        {
            return null;
        }

        return $this->mapNendoroid($nendoroid);
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): NendoroidSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query);

        return new NendoroidSearchData(
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

    private function mapListItem(Nendoroid $nendoroid): NendoroidListItemData
    {
        $baseUri = App::baseUri();

        $thumbnail = $nendoroid->thumbnail !== ''
            ? $nendoroid->thumbnail
            : null;

        $extension = $nendoroid->extension !== ''
            ? $nendoroid->extension
            : null;

        return new NendoroidListItemData(
            slug: $nendoroid->slug,
            numero: $nendoroid->numero,

            waifu: $nendoroid->waifu,
            origin: $nendoroid->origin,

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/nendoroid/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            collect: $nendoroid->collect,
        );
    }

    private function mapNendoroid(Nendoroid $nendoroid): NendoroidData
    {
        $baseUri = App::baseUri();

        $thumbnail = $nendoroid->thumbnail !== ''
            ? $nendoroid->thumbnail
            : null;

        $extension = $nendoroid->extension !== ''
            ? $nendoroid->extension
            : null;

        return new NendoroidData(
            id: $nendoroid->id,

            slug: $nendoroid->slug,
            numero: $nendoroid->numero,

            waifu: $nendoroid->waifu,
            origin: $nendoroid->origin,
            company: $nendoroid->company,

            collect: $nendoroid->collect,

            release_date: DateFormatter::display(
                $nendoroid->release_date,
            ),

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/nendoroid/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            commentaire: $nendoroid->commentaire,

            xpCollectRewarded: $nendoroid->collect_rewarded,
        );
    }

    private function mapSearchItem(
        Nendoroid $nendoroid
    ): NendoroidSearchItemData
    {
        $thumbnail = $nendoroid->thumbnail !== ''
            ? $nendoroid->thumbnail
            : null;

        $extension = $nendoroid->extension !== ''
            ? $nendoroid->extension
            : null;

        return new NendoroidSearchItemData(
            slug: $nendoroid->slug,
            numero: $nendoroid->numero,

            origin: $nendoroid->origin,
            waifu: $nendoroid->waifu,

            thumbnail: $thumbnail,
            extension: $extension,
        );
    }
}