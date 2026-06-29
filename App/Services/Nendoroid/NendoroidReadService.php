<?php

declare(strict_types=1);

namespace App\Services\Nendoroid;

use App\DTO\Nendoroid\Responses\NendoroidData;
use App\DTO\Nendoroid\Responses\NendoroidListData;
use App\Models\Nendoroid;
use App\Repositories\Nendoroid\NendoroidRepository;

use Framework\Application\App;

final readonly class NendoroidReadService
{
    public function __construct(
        private NendoroidRepository $nendoroidRepository
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

        $totalWaifus = $this->nendoroidRepository->countAll();

        if ($totalWaifus === 0)
        {
            return new NendoroidListData(
                nendoroids: [],
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

        $nendoroids = $this->nendoroidRepository->findPaginated(
            $perPage,
            $page,
        );

        return new NendoroidListData(
            nendoroids: array_map(
                $this->mapNendoroid(...),
                $nendoroids
            ),
            compteur: $totalPages,
            currentPage: $page,
            totalWaifus: $totalWaifus,
            perPage: $perPage,
        );
    }

    public function one(
        string $slug,
        int $numero
    ): ?NendoroidData
    {
        $nendoroid = $this->nendoroidRepository
            ->findOneBySlugAndNumero(
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
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function mapNendoroid(
        Nendoroid $nendoroid
    ): NendoroidData
    {
        return new NendoroidData(
            id: $nendoroid->id,

            slug: $nendoroid->slug,
            numero: $nendoroid->numero,

            waifu: $nendoroid->waifu,
            company: $nendoroid->company,

            thumbnail: $nendoroid->thumbnail !== ''
                ? $nendoroid->thumbnail
                : null,

            extension: $nendoroid->extension !== ''
                ? $nendoroid->extension
                : null,

            commentaire: $nendoroid->commentaire,
        );
    }
}