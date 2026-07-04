<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Responses;

final readonly class ChinoisVocabulaireData
{
    public function __construct(
        public int $id,
        public string $langue,
        public string $mot,
        public string $pinyin,
        public string $type,
        public string $traduction,
        public ?string $exemple,

        public bool $maitrise,
        public bool $xpRewarded,

        public bool $hasExemple,

        public string $masteredClass,
        public string $masteredValue,
        public string $masteredPressed,
        public string $masteredLabel,
    ) {
    }
}
