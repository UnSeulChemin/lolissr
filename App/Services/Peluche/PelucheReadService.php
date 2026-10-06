<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\DTO\Peluche\Responses\PelucheData;
use App\DTO\Peluche\Responses\PelucheListData;
use App\DTO\Peluche\Responses\PelucheListItemData;
use App\DTO\Peluche\Responses\PelucheSearchData;
use App\DTO\Peluche\Responses\PelucheSearchItemData;
use App\Models\Peluche\Peluche;
use App\Repositories\Peluche\PelucheCollectionRepository;
use App\Repositories\Peluche\PelucheRepository;
use App\Repositories\Peluche\PelucheSearchRepository;
use App\Services\Collections\Concerns\BuildsCollectionReadData;

use Framework\Support\Dates\DateFormatter;

final readonly class PelucheReadService
{
    use BuildsCollectionReadData;

    public function __construct(
        private PelucheRepository $pelucheRepository,
        private PelucheCollectionRepository $collectionRepository,
        private PelucheSearchRepository $searchRepository
    )
    {
    }

    // --------------------------------------------------------------------------
    // WAIFUS
    // --------------------------------------------------------------------------

    public function waifus(int|string $page = 1): ?PelucheListData
    {
        $data = $this->collectionPage(
            $page,
            $this->collectionRepository->countAll(),
            $this->collectionRepository->findPaginated(...),
            $this->mapListItem(...)
        );
        if ($data === null) return null;

        return new PelucheListData(
            peluches: $data['items'],
            currentPage: $data['currentPage'],
            totalWaifus: $data['totalItems'],
            perPage: $data['perPage'],
            totalPages: $data['totalPages']
        );
    }

    public function one(string $slug, int $numero): ?PelucheData
    {
        $peluche = $this->pelucheRepository->findOneBySlugAndNumero($slug, $numero);

        if ($peluche === null)
        {
            return null;
        }

        return $this->mapPeluche($peluche);
    }

    // --------------------------------------------------------------------------
    // RECHERCHE
    // --------------------------------------------------------------------------

    public function search(string|int $query = '', int $limit = 20): PelucheSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query, $limit);

        return new PelucheSearchData(results: \App\Support\Media\ImageAssets::withFingerprints(fn (): array => array_map($this->mapSearchItem(...), $results)), search: $query);
    }

    // --------------------------------------------------------------------------
    // CONVERSIONS
    // --------------------------------------------------------------------------

    private function mapListItem(Peluche $peluche): PelucheListItemData
    {
        $image = $this->collectionThumbnail('peluche', $peluche->thumbnail, $peluche->extension);

        return new PelucheListItemData(
            slug: $peluche->slug,
            numero: $peluche->numero,

            waifu: $peluche->waifu,
            origin: $peluche->origin,

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

            collect: $peluche->collect
        );
    }

    private function mapPeluche(Peluche $peluche): PelucheData
    {
        $image = $this->collectionThumbnail('peluche', $peluche->thumbnail, $peluche->extension);

        return new PelucheData(
            id: $peluche->id,

            slug: $peluche->slug,
            numero: $peluche->numero,

            waifu: $peluche->waifu,
            origin: $peluche->origin,
            company: $peluche->company,

            collect: $peluche->collect,

            release_date: DateFormatter::display($peluche->release_date),

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

            commentaire: $peluche->commentaire,

            xpCollectRewarded: $peluche->collect_rewarded
        );
    }

    private function mapSearchItem(Peluche $peluche): PelucheSearchItemData
    {
        $image = $this->collectionThumbnail('peluche', $peluche->thumbnail, $peluche->extension, true);

        return new PelucheSearchItemData(

            thumbnailUrl: $image['thumbnailUrl'],
            slug: $peluche->slug,
            numero: $peluche->numero,

            origin: $peluche->origin,
            waifu: $peluche->waifu,

            thumbnail: $image['thumbnail'],
            extension: $image['extension']
        );
    }
}
