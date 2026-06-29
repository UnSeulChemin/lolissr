<?php

declare(strict_types=1);

namespace App\DTO\Peluche\Responses;

final readonly class PelucheListData
{
    /**
     * @param list<PelucheData> $peluches
     */
    public function __construct(
        public array $peluches,
        public int $compteur,
        public int $currentPage,
        public int $totalWaifus,
        public int $perPage,
    ) {
    }
}