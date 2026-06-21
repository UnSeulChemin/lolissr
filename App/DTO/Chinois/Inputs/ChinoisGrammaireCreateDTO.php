<?php

declare(strict_types=1);

namespace App\DTO\Chinois\Inputs;

use Framework\Support\Str;

final readonly class ChinoisGrammaireCreateDTO
{
    public function __construct(
        public string $niveau,
        public string $titre,
        public string $structure,
        public ?string $abreviation,
        public string $phrase,
        public string $pinyin,
        public string $traduction,
        public ?string $explication,
        public string $section,
        public string $categorie
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            niveau: trim((string) ($data['niveau'] ?? '')),
            titre: trim((string) ($data['titre'] ?? '')),
            structure: trim((string) ($data['structure'] ?? '')),
            abreviation: Str::nullableTrim($data['abreviation'] ?? null),
            phrase: trim((string) ($data['phrase'] ?? '')),
            pinyin: trim((string) ($data['pinyin'] ?? '')),
            traduction: trim((string) ($data['traduction'] ?? '')),
            explication: Str::nullableTrim($data['explication'] ?? null),
            section: trim((string) ($data['section'] ?? '')),
            categorie: trim((string) ($data['categorie'] ?? ''))
        );
    }
}
