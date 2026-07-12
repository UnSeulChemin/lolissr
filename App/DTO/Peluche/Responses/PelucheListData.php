<?php

declare(strict_types=1);

namespace App\DTO\Peluche\Responses;

final readonly class PelucheListData
{
    /**
     * @param list<PelucheListItemData> $peluches
     */
    public function __construct(
        public array $peluches,
        public int $currentPage,
        public int $totalWaifus,
        public int $perPage,
        public int $totalPages,
    ) {
    }
}