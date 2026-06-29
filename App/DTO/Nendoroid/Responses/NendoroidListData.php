<?php

declare(strict_types=1);

namespace App\DTO\Nendoroid\Responses;

final readonly class NendoroidListData
{
    /**
     * @param list<NendoroidData> $nendoroids
     */
    public function __construct(
        public array $nendoroids,
        public int $compteur,
        public int $currentPage,
        public int $totalWaifus,
        public int $perPage,
    ) {
    }
}