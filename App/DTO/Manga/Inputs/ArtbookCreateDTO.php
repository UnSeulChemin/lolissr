<?php

declare(strict_types=1);

namespace App\DTO\Manga\Inputs;

use Framework\Support\Str;

final readonly class ArtbookCreateDTO
{
    public function __construct(
        public string $artbook,
        public ?string $auteur,
        public ?string $serie,
        public string $slug,
        public int $numero
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $artbook = trim((string) ($data['artbook'] ?? ''));

        return new self(
            artbook: $artbook,
            auteur: Str::nullableTrim($data['auteur'] ?? null),
            serie: Str::nullableTrim($data['serie'] ?? null),
            slug: Str::slug((string) ($data['slug'] ?? $artbook)),
            numero: max(1, (int) ($data['numero'] ?? 1)),
        );
    }
}