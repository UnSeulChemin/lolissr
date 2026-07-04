<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisHskData
{
    /**
     * @param list<ChinoisSectionData> $sections
     */
    public function __construct(
        public string $level,
        public string $description,
        public string $sourceUrl,
        public string $sourceDescription,
        public array $sections,
    ) {
    }
}
