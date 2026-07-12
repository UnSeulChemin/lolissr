<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\DTO\Peluche\Responses\PelucheData;
use App\DTO\Peluche\Responses\PelucheListData;
use App\DTO\Peluche\Responses\PelucheListItemData;
use App\DTO\Peluche\Responses\PelucheSearchData;
use App\DTO\Peluche\Responses\PelucheSearchItemData;
use App\Models\Peluche;
use App\Repositories\Peluche\PelucheCollectionRepository;
use App\Repositories\Peluche\PelucheRepository;
use App\Repositories\Peluche\PelucheSearchRepository;

use Framework\Application\App;
use Framework\Support\DateFormatter;

final readonly class PelucheReadService
{
    public function __construct(
        private PelucheRepository $pelucheRepository,
        private PelucheCollectionRepository $collectionRepository,
        private PelucheSearchRepository $searchRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | WAIFUS
    |--------------------------------------------------------------------------
    */

    public function waifus(int|string $page = 1): ?PelucheListData
    {
        $page = max(1, (int) $page);

        $perPage = App::pagination();

        $totalWaifus = $this->collectionRepository->countAll();

        if ($totalWaifus === 0)
        {
            return new PelucheListData(
                peluches: [],
                currentPage: 1,
                totalWaifus: 0,
                perPage: $perPage,
                totalPages: 1,
            );
        }

        $totalPages = (int) ceil($totalWaifus / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $peluches = $this->collectionRepository->findPaginated(
            $perPage,
            $page,
        );

        return new PelucheListData(
            peluches: array_map(
                $this->mapListItem(...),
                $peluches
            ),
            currentPage: $page,
            totalWaifus: $totalWaifus,
            perPage: $perPage,
            totalPages: $totalPages,
        );
    }

    public function one(string $slug, int $numero): ?PelucheData
    {
        $peluche = $this->pelucheRepository->findOneBySlugAndNumero(
            $slug,
            $numero
        );

        if ($peluche === null)
        {
            return null;
        }

        return $this->mapPeluche($peluche);
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): PelucheSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query);

        return new PelucheSearchData(
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

    private function mapListItem(Peluche $peluche): PelucheListItemData
    {
        $baseUri = App::baseUri();

        $thumbnail = $peluche->thumbnail !== ''
            ? $peluche->thumbnail
            : null;

        $extension = $peluche->extension !== ''
            ? $peluche->extension
            : null;

        return new PelucheListItemData(
            slug: $peluche->slug,
            numero: $peluche->numero,

            waifu: $peluche->waifu,
            origin: $peluche->origin,

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/peluche/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            collect: $peluche->collect,
        );
    }

    private function mapPeluche(Peluche $peluche): PelucheData
    {
        $baseUri = App::baseUri();

        $thumbnail = $peluche->thumbnail !== ''
            ? $peluche->thumbnail
            : null;

        $extension = $peluche->extension !== ''
            ? $peluche->extension
            : null;

        return new PelucheData(
            id: $peluche->id,

            slug: $peluche->slug,
            numero: $peluche->numero,

            waifu: $peluche->waifu,
            origin: $peluche->origin,
            company: $peluche->company,

            collect: $peluche->collect,

            release_date: DateFormatter::display(
                $peluche->release_date,
            ),

            thumbnail: $thumbnail,
            extension: $extension,

            thumbnailUrl:
                $thumbnail !== null && $extension !== null
                    ? "{$baseUri}images/peluche/thumbnail/{$thumbnail}.{$extension}"
                    : null,

            commentaire: $peluche->commentaire,

            xpCollectRewarded: $peluche->collect_rewarded,
        );
    }

    private function mapSearchItem(Peluche $peluche): PelucheSearchItemData
    {
        $thumbnail = $peluche->thumbnail !== ''
            ? $peluche->thumbnail
            : null;

        $extension = $peluche->extension !== ''
            ? $peluche->extension
            : null;

        return new PelucheSearchItemData(
            slug: $peluche->slug,
            numero: $peluche->numero,

            origin: $peluche->origin,
            waifu: $peluche->waifu,

            thumbnail: $thumbnail,
            extension: $extension,
        );
    }
}