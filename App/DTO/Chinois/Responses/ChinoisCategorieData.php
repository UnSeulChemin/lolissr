<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisCategorieData
{
    /**
     * @param list<ChinoisGrammaireData> $grammaires
     */
    public function __construct(
        public string $title,
        public array $grammaires,
    ) {
    }
}
