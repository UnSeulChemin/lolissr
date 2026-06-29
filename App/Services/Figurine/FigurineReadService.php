<?php

declare(strict_types=1);

namespace App\Services\Figurine;

use App\Models\Figurine;
use App\DTO\Figurine\Responses\FigurineData;
use App\DTO\Figurine\Responses\FigurineListData;
use App\Repositories\Figurine\FigurineRepository;

use Framework\Application\App;

final readonly class FigurineReadService
{
    public function __construct(
        private FigurineRepository $figurineRepository
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

        $totalWaifus = $this->figurineRepository->countAll();

        if ($totalWaifus === 0)
        {
            return null;
        }

        $totalPages = (int) ceil($totalWaifus / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $figurines = $this->figurineRepository->findPaginated(
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

    public function one(
        string $slug,
        int $numero
    ): ?FigurineData
    {
        $figurine = $this->figurineRepository
            ->findOneBySlugAndNumero($slug, $numero);

        if ($figurine === null)
        {
            return null;
        }

        return $this->mapFigurine($figurine);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapFigurine(Figurine $figurine): FigurineData
    {
        return new FigurineData(
            id: $figurine->id,

            slug: $figurine->slug,
            numero: $figurine->numero,

            waifu: $figurine->waifu,
            company: $figurine->company,

            thumbnail: $figurine->thumbnail !== '' ? $figurine->thumbnail : null,
            extension: $figurine->extension !== '' ? $figurine->extension : null,

            commentaire: $figurine->commentaire,
        );
    }
}