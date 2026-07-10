<?php

declare(strict_types=1);

namespace App\DTO\Nendoroid\Responses;

final readonly class NendoroidSearchData
{
    /**
     * @param list<NendoroidSearchItemData> $results
     */
    public function __construct(
        public array $results,
        public string $search,
    ) {
    }
}