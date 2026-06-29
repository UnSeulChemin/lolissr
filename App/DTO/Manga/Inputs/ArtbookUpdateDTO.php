<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use Framework\Support\Str;

final readonly class ArtbookUpdateDTO
{
    public function __construct(
        public string $artbook,
        public ?string $auteur,
        public ?string $serie,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            artbook: trim((string) ($data['artbook'] ?? '')),
            auteur: Str::nullableTrim($data['auteur'] ?? null),
            serie: Str::nullableTrim($data['serie'] ?? null),
        );
    }
}