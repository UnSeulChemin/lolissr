<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\DTO\Figurine\Responses\FigurineData;
use App\DTO\Figurine\Responses\FigurineListData;
use App\DTO\Figurine\Responses\FigurineListItemData;
use App\DTO\Figurine\Responses\FigurineSearchData;
use App\DTO\Figurine\Responses\FigurineSearchItemData;
use App\Models\Figurine\Figurine;
use App\Repositories\Figurine\FigurineCollectionRepository;
use App\Repositories\Figurine\FigurineRepository;
use App\Repositories\Figurine\FigurineSearchRepository;
use App\Services\Collections\Concerns\BuildsCollectionReadData;

use Framework\Support\Dates\DateFormatter;

final readonly class FigurineReadService
{
    use BuildsCollectionReadData;

    public function __construct(
        private FigurineRepository $figurineRepository,
        private FigurineCollectionRepository $collectionRepository,
        private FigurineSearchRepository $searchRepository
    )
    {
    }

    // --------------------------------------------------------------------------
    // WAIFUS
    // --------------------------------------------------------------------------

    public function waifus(int|string $page = 1): ?FigurineListData
    {
        $data = $this->collectionPage(
            $page,
            $this->collectionRepository->countAll(),
            $this->collectionRepository->findPaginated(...),
            $this->mapSeriesItem(...)
        );
        if ($data === null) return null;

        return new FigurineListData(
            figurines: $data['items'],
            currentPage: $data['currentPage'],
            totalWaifus: $data['totalWaifus'],
            perPage: $data['perPage'],
            totalPages: $data['totalPages']
        );
    }

    public function one(string $slug, int $numero): ?FigurineData
    {
        $figurine = $this->figurineRepository->findOneBySlugAndNumero($slug, $numero);

        if ($figurine === null)
        {
            return null;
        }

        return $this->mapFigurine($figurine);
    }

    // --------------------------------------------------------------------------
    // RECHERCHE
    // --------------------------------------------------------------------------

    public function search(string|int $query = ''): FigurineSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query);

        return new FigurineSearchData(results: array_map($this->mapSearchItem(...), $results), search: $query);
    }

    // --------------------------------------------------------------------------
    // CONVERSIONS
    // --------------------------------------------------------------------------

    private function mapSeriesItem(Figurine $figurine): FigurineListItemData
    {
        $image = $this->collectionThumbnail('figurine', $figurine->thumbnail, $figurine->extension);

        return new FigurineListItemData(
            slug: $figurine->slug,
            numero: $figurine->numero,

            waifu: $figurine->waifu,
            origin: $figurine->origin,

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

            collect: $figurine->collect
        );
    }

    private function mapFigurine(Figurine $figurine): FigurineData
    {
        $image = $this->collectionThumbnail('figurine', $figurine->thumbnail, $figurine->extension);

        return new FigurineData(
            id: $figurine->id,

            slug: $figurine->slug,
            numero: $figurine->numero,

            origin: $figurine->origin,
            waifu: $figurine->waifu,
            scale: $figurine->scale,
            height_cm: $figurine->height_cm,
            company: $figurine->company,

            collect: $figurine->collect,

            release_date: DateFormatter::display($figurine->release_date),

            thumbnail: $image['thumbnail'],
            extension: $image['extension'],

            thumbnailUrl: $image['thumbnailUrl'],

            commentaire: $figurine->commentaire,

            xpCollectRewarded: $figurine->collect_rewarded
        );
    }

    private function mapSearchItem(Figurine $figurine): FigurineSearchItemData
    {
        $image = $this->collectionThumbnail('figurine', $figurine->thumbnail, $figurine->extension, true);

        return new FigurineSearchItemData(

            thumbnailUrl: $image['thumbnailUrl'],
            slug: $figurine->slug,
            numero: $figurine->numero,

            origin: $figurine->origin,
            waifu: $figurine->waifu,

            thumbnail: $image['thumbnail'],
            extension: $image['extension']
        );
    }
}
