<?php

declare(strict_types=1);

namespace App\DTO\Peluche\Responses;

final readonly class PelucheSearchData
{
    /**
     * @param list<PelucheSearchItemData> $results
     */
    public function __construct(
        public array $results,
        public string $search,
    ) {
    }
}