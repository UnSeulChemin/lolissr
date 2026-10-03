<?php

declare(strict_types=1);

namespace App\DTO\Artbook\Responses;

final readonly class ArtbookSearchData
{
    /**
     * @param list<ArtbookSearchItemData> $results
     */
    public function __construct(public array $results, public string $search)
    {
    }
}