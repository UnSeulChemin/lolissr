<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\DTO\Figurine\Responses\FigurineData;
use App\DTO\Figurine\Responses\FigurineListData;
use App\DTO\Figurine\Responses\FigurineSearchData;
use App\DTO\Figurine\Responses\FigurineSearchItemData;
use App\Models\Figurine;
use App\Repositories\Figurine\FigurineRepository;
use App\Repositories\Figurine\FigurineSearchRepository;
use App\Repositories\Figurine\FigurineCollectionRepository;

use Framework\Application\App;
use Framework\Support\DateFormatter;

final readonly class FigurineReadService
{
    public function __construct(
        private FigurineRepository $figurineRepository,
        private FigurineCollectionRepository $collectionRepository,
        private FigurineSearchRepository $searchRepository,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | WAIFUS
    |--------------------------------------------------------------------------
    */

    public function waifus(int|string $page = 1): ?FigurineListData
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

        $figurines = $this->collectionRepository->findPaginated(
            $perPage,
            $page,
        );

        return new FigurineListData(
            figurines: array_map($this->mapFigurine(...), $figurines),
            compteur: $totalPages,
            currentPage: $page,
            totalWaifus: $totalWaifus,
            perPage: $perPage,
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

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(string|int $query = ''): FigurineSearchData
    {
        $query = trim((string) $query);

        $results = $this->searchRepository->search($query);

        return new FigurineSearchData(results: array_map($this->mapSearchItem(...), $results), search: $query);
    }

    /*
    |--------------------------------------------------------------------------
    | MAPPERS
    |--------------------------------------------------------------------------
    */

    private function mapFigurine(Figurine $figurine): FigurineData
    {
        return new FigurineData(
            id: $figurine->id,

            slug: $figurine->slug,
            numero: $figurine->numero,

            origin: $figurine->origin,
            waifu: $figurine->waifu,
            scale: $figurine->scale,
            height_cm: $figurine->height_cm,
            company: $figurine->company,
            release_date: DateFormatter::display(
                $figurine->release_date,
            ),

            thumbnail: $figurine->thumbnail,
            extension: $figurine->extension,

            commentaire: $figurine->commentaire,
        );
    }

    private function mapSearchItem(Figurine $figurine): FigurineSearchItemData
    {
        return new FigurineSearchItemData(
            slug: $figurine->slug,
            numero: $figurine->numero,
            origin: $figurine->origin,
            waifu: $figurine->waifu,
            thumbnail: $figurine->thumbnail !== '' ? $figurine->thumbnail : null,
            extension: $figurine->extension !== '' ? $figurine->extension : null,
        );
    }
}
