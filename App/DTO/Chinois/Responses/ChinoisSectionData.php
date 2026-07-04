<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisSectionData
{
    /**
     * @param list<ChinoisCategorieData> $categories
     */
    public function __construct(
        public string $title,
        public string $id,
        public array $categories,
    ) {
    }
}
