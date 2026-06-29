<?php

declare(strict_types=1);

namespace App\Services\Peluche;

use App\Models\Peluche;
use App\DTO\Peluche\Responses\PelucheData;
use App\DTO\Peluche\Responses\PelucheListData;
use App\Repositories\Peluche\PelucheRepository;

use Framework\Application\App;

final readonly class PelucheReadService
{
    public function __construct(
        private PelucheRepository $pelucheRepository
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

        $totalWaifus = $this->pelucheRepository->countAll();

        if ($totalWaifus === 0)
        {
            return new PelucheListData(
                peluches: [],
                compteur: 1,
                currentPage: 1,
                totalWaifus: 0,
                perPage: $perPage,
            );
        }

        $totalPages = (int) ceil($totalWaifus / $perPage);

        if ($page > $totalPages)
        {
            return null;
        }

        $peluches = $this->pelucheRepository->findPaginated(
            $perPage,
            $page,
        );

        return new PelucheListData(
            peluches: array_map($this->mapPeluche(...), $peluches),
            compteur: $totalPages,
            currentPage: $page,
            totalWaifus: $totalWaifus,
            perPage: $perPage,
        );
    }

    public function one(
        string $slug,
        int $numero
    ): ?PelucheData
    {
        $peluche = $this->pelucheRepository
            ->findOneBySlugAndNumero($slug, $numero);

        if ($peluche === null)
        {
            return null;
        }

        return $this->mapPeluche($peluche);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapPeluche(Peluche $peluche): PelucheData
    {
        return new PelucheData(
            id: $peluche->id,

            slug: $peluche->slug,
            numero: $peluche->numero,

            waifu: $peluche->waifu,
            company: $peluche->company,

            thumbnail: $peluche->thumbnail !== ''
                ? $peluche->thumbnail
                : null,

            extension: $peluche->extension !== ''
                ? $peluche->extension
                : null,

            commentaire: $peluche->commentaire,
        );
    }
}