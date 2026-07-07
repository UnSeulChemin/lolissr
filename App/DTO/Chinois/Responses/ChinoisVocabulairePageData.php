<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisVocabulairePageData
{
    /**
     * @param list<ChinoisVocabulaireData> $vocabulaires
     */
    public function __construct(
        public array $vocabulaires,
        public int $currentPage,
        public int $totalVocabulaires,
        public int $perPage,
        public int $totalPages,
    ) {
    }
}